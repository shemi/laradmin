<?php

namespace Shemi\Laradmin;

use Illuminate\Contracts\Filesystem\Filesystem;
use Route;
use Shemi\Laradmin\Data\DataManager;
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

    /**
     * @var RoleSystems\RoleSystem;
     */
    protected $roleSystem;

    /**
     * @var DataManager
     */
    protected $dataManager;

    public function __construct()
    {
        $this->filesystem = app(Filesystem::class);

        $roleSystem = "\\Shemi\\Laradmin\\RoleSystems\\" . studly_case(config('laradmin.roles.system', 'simple'));
        $this->roleSystem = new $roleSystem();

        $this->dataManager = new DataManager($this->filesystem);
    }

    public function routes()
    {
        Route::group(['as' => 'laradmin.'], function() {
            require __DIR__.'/../routes/laradmin.php';
        });
    }

    /**
     * @return DataManager
     */
    public function data()
    {
        return $this->dataManager;
    }

    public function model($name)
    {
        return app($this->models[studly_case($name)]);
    }

    public function modelClass($name)
    {
        return $this->models[$name];
    }

    public function registerPolicies()
    {
        $this->roleSystem->registerPolicies();
    }

    public function getRoleSystem()
    {
        return $this->roleSystem;
    }

}