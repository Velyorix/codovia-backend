<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Transport\NodePool\StaticNoPingNodePool;
use Elastic\Transport\NodePool\NodePoolInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NodePoolInterface::class, function () {
            return new StaticNoPingNodePool([]);
        });

        $this->app->singleton('elasticsearch', function () {
            return ClientBuilder::create()
                ->setHosts([config('scout.elasticsearch.hosts')])
                ->build();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Charger les routes API
        Route::prefix('api')
            ->middleware('api')
            ->namespace('App\Http\Controllers')
            ->group(base_path('routes/api.php'));

        // Charger les routes Web
        Route::middleware('web')
            ->namespace('App\Http\Controllers')
            ->group(base_path('routes/web.php'));
    }
}
