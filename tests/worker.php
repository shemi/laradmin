<?php

require __DIR__.'/../vendor/autoload.php';

use Orchestra\Testbench\Traits\CreatesApplication;

$appLoader = new class {
    use CreatesApplication;
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Define your environment setup.
    }
};

// Configure the application...
$app = $appLoader->createApplication();
$app->register(Shemi\Laradmin\LaradminServiceProvider::class);