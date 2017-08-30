<?php

namespace Shemi\Laradmin;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Route;
use Shemi\Laradmin\Data\DataManager;
use Shemi\Laradmin\FormFields\FieldContract;
use Shemi\Laradmin\Models\Field;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Models\User;

class Laradmin
{

    protected $formFields = [];

    protected $models = [
        'User' => User::class
    ];

    protected $jsObject = [];

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    public $filesystem;

    /**
     * @var RoleSystems\RoleSystem;
     */
    protected $roleSystem;

    public function __construct()
    {
        $this->filesystem = app(Filesystem::class);

        $roleSystem = "\\Shemi\\Laradmin\\RoleSystems\\" . studly_case(config('laradmin.roles.system', 'simple'));
        $this->roleSystem = new $roleSystem();
    }

    public function filesystem()
    {
        return $this->filesystem;
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

    public function formFieldExists($type)
    {
        return isset($this->formFields[$type]);
    }

    public function formField($type)
    {
        return $this->formFields[$type];
    }

    public function addFormField($fieldClass)
    {
        if(! ($fieldClass instanceof FieldContract)) {
            $fieldClass = app($fieldClass);
        }

        $this->formFields[$fieldClass->getCodename()] = $fieldClass;

        return $this;
    }

    public function initJsObject()
    {
        $this->jsObject = [
            'api_base' => route('laradmin.dashboard'),
            'routs' => [
                'icons' => route('laradmin.icons')
            ],
            'mixins' => []
        ];
    }

    public function publishJs($key, $value)
    {
        array_set($this->jsObject, $key, $value);

        return $this;
    }

    public function publishManyJs($array)
    {
        foreach ($array as $key => $value) {
            $this->publishJs($key, $value);
        }

        return $this;
    }

    public function jsObject()
    {
        return json_encode($this->jsObject, JSON_UNESCAPED_UNICODE);
    }

}