<?php
namespace Successive\ElasticSearch\Http;

use Illuminate\Routing\Controller as BaseController;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

class ElasticSearchController extends BaseController
{
    protected $client = null;
    protected $pageSize = 50; 
    protected $totalPages = 1; 
    protected $totalDocuments = 0; 




    public function __construct() {
        $this->client = ClientBuilder::create()->setHosts([env("ELASTICSEARCH_HOST").':'.env('ELASTICSEARCH_PORT')])->build();
    }
    
    /*
     * Function is use to validate index
     */
    public function validateIndex($index) {
        if(!$index || !$this->client->indices()->exists(array('index' => $index))) {
            return [
                        'success' => false,
                        'message' => 'Invalid index name.',
                        'data' => array()
            ];
        }
        
        return [
                    'success' => true,
                    'message' => 'Valid index name.'
        ];
    }

    public function index()
    {   
        try {
            $response = $this->client->cat()->indices();
        
            return response()->json([
                        'success' => true,
                        'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
            ]);
        }
        
    }
    
    /*
     * Function is use to get all the types of an index
     */
    public function getIndexTypes(Request $request) {
        
        try {
            $types = array();
        
            $index = !empty($request->input('index')) ? $request->input('index') : '';

            $indexResponse = $this->validateIndex($index);
            if(!$indexResponse['success']) {
                return response()->json($indexResponse);
            }

            $response = $this->client->indices()->getMapping(array('index' => $index));

            if(!empty($response[$index]['mappings'])) {
                $types = array_keys($response[$index]['mappings']);
            }
            
            return response()->json([
                        'success' => true,
                        'data' => $types
            ]);
        } catch (\Exception $e) {
            return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
            ]);
        }
        
    }
    
    /*
     * Function is use to get index fields
     */
    public function getIndexFields(Request $request) {
        try {
            $fields = array();
            $index = !empty($request->input('index')) ? $request->input('index') : '';
            $type = !empty($request->input('type')) ? $request->input('type') : '';

            $indexResponse = $this->validateIndex($index);
            if(!$indexResponse['success']) {
                return response()->json($indexResponse);
            }

            $response = $this->client->indices()->getMapping(array('index' => $index));
            
            if(!empty($response[$index]['mappings'][$type]['properties'])) {
                foreach($response[$index]['mappings'][$type]['properties'] as $fieldName => $mapping) {
                    $sortable = true;
                    
                    if($mapping['type'] == 'text' && (!isset($mapping['fielddata']) || !$mapping['fielddata'])) {
                        $sortable = false;
                    }
                    $fields[] = array('field' => $fieldName, 'sortable' => $sortable);
                }
                
            }
            
            return response()->json([
                        'success' => true,
                        'data' => $fields
            ]);
        } catch (\Exception $e) {
            return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
            ]);
        }
        
    }
    
    public function getDocumentFromIndex(Request $request) {
        try {
            $result = array();
            
            $index = !empty($request->input('index')) ? $request->input('index') : '';
            $type = !empty($request->input('type')) ? $request->input('type') : '';
            $page = !empty($request->input('page')) ? $request->input('page') : 1;
            $fields = !empty($request->input('fields')) ? $request->input('fields') : [];
            $search = !empty($request->input('search')) ? $request->input('search') : '';
            $sorters = !empty($request->input('sorters')) ? $request->input('sorters') : '';
            $page--;
            
            
            
            $sortParams = array();
            if($sorters) {
                foreach ($sorters as $sorter) {
                    $sortParams[$sorter['field']] =  ['order' => $sorter['dir']];
                }
            }
                        
            $queryBody = ["match_all" => new \stdClass()];
            
            if($search) {
                
                $queryBody = [
                    "match" => [
                        "_all" => $search
                    ]
                ];
                
            }
            
            $this->getPaginationDetail($request);
            
            $indexResponse = $this->validateIndex($index);
            if(!$indexResponse['success']) {
                return response()->json($indexResponse);
            }
            
            $params = [
                "from" => $page * $this->pageSize,  
                "size" => $this->pageSize,              
                "index" => $index,
                "type" => $type,
                "_source_include" => $fields,
                "body" => [
                    
                    "query" => $queryBody,
                    "sort" => $sortParams
                ]
            ];

            // Execute the search
            $response = $this->client->search($params);
            
            if(!empty($response['hits']['hits'])) {
                foreach ($response['hits']['hits'] as $data) {
                    $data['_source']['id'] = $data['_id'];
                    $result[] = $data['_source'];
                }
            }
            
            return response()->json([
                        'success' => true,
                        'data' => $result,
                        'last_page' => $this->totalPages ? $this->totalPages : 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
            ]);
        }
        
    }
    
    /*
     * Function is use to get pagination details and update the max_result_window to the total number
     * of documents in an index
     */
    public function getPaginationDetail(Request $request) {
        try {
            $index = !empty($request->input('index')) ? $request->input('index') : '';
            $type = !empty($request->input('type')) ? $request->input('type') : '';
            $search = !empty($request->input('search')) ? $request->input('search') : '';
            
            $queryBody = ["match_all" => new \stdClass()];
                        
            if($search) {
                $queryBody = [
                    "match" => [
                        "_all" => $search
                    ]
                ];
                
            }

            $indexResponse = $this->validateIndex($index);
            if(!$indexResponse['success']) {
                return response()->json($indexResponse);
            }

            $params = [             
                "index" => $index,
                "type" => $type,
                "body" => [
                    "query" => $queryBody
                ]
            ];

            $response = $this->client->search($params);

            if(!empty($response['hits']['total']) && $response['hits']['total'] > 0) {
                $this->totalDocuments = $response['hits']['total'];

                $this->totalPages = floor($this->totalDocuments / $this->pageSize);

                //if documents are more than default value then update
                if($this->totalDocuments > 10000) {
                    $this->updateSetting($index);
                } else if($this->totalDocuments <= 10000) {
                    $this->updateSetting($index, 10000);
                }  
                    
            }
            
            return response()->json([
                        'success' => true,
                        'data' => $this->totalPages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
            ]);
        }
        
        
    }
    
    /*
     * Function is use to update the max_result_window setting of an index
     */
    public function updateSetting($index, $totalDocuments = 0) {
        $indexResponse = $this->validateIndex($index);
        if(!$indexResponse['success']) {
            return response()->json($indexResponse);
        }
        
        $params = [             
            "index" => $index,
            "body" => [
                "max_result_window" => $totalDocuments ? $totalDocuments : $this->totalDocuments
            ]
        ];

        $response = $this->client->indices()->putSettings($params);
        
        return $response['acknowledged'];        
    }
}