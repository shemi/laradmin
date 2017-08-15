<?php

namespace Shemi\Laradmin;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Shemi\Laradmin\Console\Commands\AdminCommand;
use Shemi\Laradmin\Console\Commands\InstallCommand;
use Shemi\Laradmin\Console\Commands\RolesCommand;
use Shemi\Laradmin\Facades\Laradmin as LaradminFacade;
use Shemi\Laradmin\Http\Middleware\RedirectIfAuthenticated;
use Shemi\Laradmin\Http\Middleware\RedirectIfCantAdmin;

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

        $this->app->register(\Spatie\Permission\PermissionServiceProvider::class);
        $this->app->register(\Spatie\MediaLibrary\MediaLibraryServiceProvider::class);

        $this->loadHelpers();
        $this->registerConfigs();
        $this->registerFormFields();

        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
        }
    }

    public function boot(Router $router, Dispatcher $event)
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laradmin');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laradmin');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        LaradminFacade::registerPolicies();

        if (app()->version() >= 5.4) {
            $router->aliasMiddleware('laradmin.gust', RedirectIfAuthenticated::class);
            $router->aliasMiddleware('laradmin.user.admin', RedirectIfCantAdmin::class);
        } else {
            $router->middleware('laradmin.gust', RedirectIfAuthenticated::class);
            $router->middleware('laradmin.user.admin', RedirectIfCantAdmin::class);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/laradmin.php');

        LaradminFacade::initJsObject();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                RolesCommand::class,
                AdminCommand::class
            ]);
        }
    }

    /**
     * Load helpers.
     */
    protected function loadHelpers()
    {
        foreach (glob(__DIR__.'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }

    protected function registerConfigs()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/publishable/config/laradmin.php', 'laradmin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/publishable/config/medialibrary.php', 'laradmin-medialibrary'
        );
    }

    protected function registerFormFields()
    {
        $formFields = [
            'input',
            'select_multiple',
            'checkboxes',
            'repeater',
            'switch',
            'files',
        ];

        foreach ($formFields as $formField) {
            $class = studly_case("{$formField}_Field");

            LaradminFacade::addFormField("Shemi\\Laradmin\\FormFields\\{$class}");
        }

        event('laradmin::form-fields.registered');
    }

    /**
     * Register the publishable files.
     */
    protected function registerPublishableResources()
    {
        $publishablePath = dirname(__DIR__).'/publishable';

        $publishable = [
            'laradmin_assets' => [
                "{$publishablePath}/public/" => public_path(config('laradmin.assets_path')),
            ],
            'laradmin_config' => [
                "{$publishablePath}/config/laradmin.php" => config_path('laradmin.php'),
            ],
            'laradmin_data' => [
                "{$publishablePath}/data/" => storage_path('app/laradmin'),
            ]
        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

}