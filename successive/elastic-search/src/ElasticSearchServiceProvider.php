<?php
namespace Successive\ElasticSearch;

use Illuminate\Support\ServiceProvider;

class ElasticSearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('successive-elastic-search', function() {
            return new ElasticSearch;
        });
    }

    public function boot()
    {
        require __DIR__ . '/Http/routes.php';
        
        $this->loadViewsFrom(__DIR__ . '/views', 'successive-elastic-search');
    }
}