<?php

//get all the indices of elastic search
Route::get('successive/elastic-index', '\Successive\ElasticSearch\Http\ElasticSearchController@index')->name('indices');

//Get all the types of an index
Route::get('successive/elastic-index-types', '\Successive\ElasticSearch\Http\ElasticSearchController@getIndexTypes');

//Get all the fields of an index by its type
Route::get('successive/elastic-index-fields', '\Successive\ElasticSearch\Http\ElasticSearchController@getIndexFields');

//Get data from an index
Route::get('successive/elastic-index-document', '\Successive\ElasticSearch\Http\ElasticSearchController@getDocumentFromIndex');

//Get pagination details
Route::get('successive/elastic-pagination-detail', '\Successive\ElasticSearch\Http\ElasticSearchController@getPaginationDetail');

//Display the tabulator view for elastic
Route::get('successive/elastic-document-view', function () {
    return view('successive-elastic-search::index');
})->name('successive-elastic-view');
