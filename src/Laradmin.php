<?php

namespace Shemi\Laradmin;

use Illuminate\Contracts\Filesystem\Filesystem;
use Route;
use Shemi\Laradmin\Models\User;

class Laradmin
{

    protected $models = [
        'User' => User::class
    ];

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    public $filesystem;

    public function __construct()
    {
        $this->filesystem = app(Filesystem::class);
    }

    public function routes()
    {
        Route::group(['as' => 'laradmin.'], function() {
            require __DIR__.'/../routes/laradmin.php';
        });
    }

    public function model($name)
    {
        return app($this->models[studly_case($name)]);
    }

    public function modelClass($name)
    {
        return $this->models[$name];
    }

}