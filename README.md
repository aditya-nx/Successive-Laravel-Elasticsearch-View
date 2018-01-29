# Successive Laravel Elasticsearch View
This package will facilitate the view to render the Elasticsearch documents along with the following operations.

 - Sorting
 - Searching
 - Pagination

# Requirements
 - PHP >= 5.6
 - Laravel 5.4|5.5
 
# Dependency
* [Tabulator](http://tabulator.info/)
* [Bootstrap](https://getbootstrap.com/)
* [Jquery Ui](https://jqueryui.com/)
* [Jquery](https://jquery.com/)

# Setup

- ###### Include following files in your project from assets folder

    - successive-elastic.js
    - jquery.min.js
    - jquery-ui.min.js
    - tabulator.min.js
    - bootstrap.min.css
    - tabulator.min.css
    
```sh
Note: Include jquery and jquery ui before including tabulator files.
```
    
    
- ######  Register for the dependency in config/app.php
   Successive\ElasticSearch\ElasticSearchServiceProvider::class

- ###### Define the namespace in composer.json under autoload psr-4 section
  "Successive\\\ElasticSearch\\\\": "vendor/successive/elastic-search/src/"

- ######  Define Elasticsearch host and port in your .env file
     ELASTICSEARCH_HOST=127.0.0.1
     ELASTICSEARCH_PORT=9200

  
- ##### Include Elasticsearch view in your filename.blade.php inside content seaction

```sh
@section('content')
<div class="row">
    <div class="col-md-12">
	    //This will display the Elasticsearch view
        @include('successive-elastic-search::index') 
    </div>
</div>
```

##### Powered by [Successive Software](http://www.successivesoftwares.com/)
