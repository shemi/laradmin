<?php

namespace Shemi\Laradmin;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Shemi\Laradmin\Facades\Laradmin as LaradminFacade;

class LaradminServiceProvider extends ServiceProvider
{

    /**
     * Register the application service
     */
    public function register()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Laradmin', LaradminFacade::class);

        $this->app->singleton('laradmin', function() {
            return new Laradmin();
        });

        $this->registerConfigs();

        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
        }
    }

    public function boot(Router $router, Dispatcher $event)
    {

        $this->loadViewsFrom(__DIR__.'../resources/views', 'laradmin');

    }

    public function registerConfigs()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/publishable/config/laradmin.php', 'laradmin'
        );
    }

    /**
     * Register the publishable files.
     */
    private function registerPublishableResources()
    {
        $publishablePath = dirname(__DIR__).'/publishable';

        $publishable = [
            'laradmin_assets' => [
                "{$publishablePath}/assets/" => public_path(config('laradmin.assets_path')),
            ],
            'config' => [
                "{$publishablePath}/config/laradmin.php" => config_path('laradmin.php'),
            ],
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

}