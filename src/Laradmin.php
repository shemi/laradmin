<?php

namespace Shemi\Laradmin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Route;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;
use Shemi\Laradmin\Exceptions\InvalidManagerException;
use Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException;
use Shemi\Laradmin\Managers\FormFieldsManager;
use Shemi\Laradmin\Managers\FormPanelsManager;
use Shemi\Laradmin\Managers\LinksManager;
use Shemi\Laradmin\Managers\RolesManager;
use Shemi\Laradmin\Managers\WidgetsManager;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Models\User;

class Laradmin
{
    const VERSION = "0.6.5";

    protected $models = [
        'User' => User::class
    ];

    protected $managers = [];

    protected $jsObject = [];

    public function init()
    {
        $this->registerManagers()
            ->registerFormPanels()
            ->registerFormFields();
    }

    public function user()
    {
        return Auth::guard(config('laradmin.guard'))->user();
    }

    public function getUserType()
    {
        if(! $user = $this->user()) {
            return null;
        }

        $type = Type::filter(function($type) use ($user) {
            $typeModel = trim($type->model, '\\');
            $userModel = trim(get_class($user), '\\');

            return $typeModel == $userModel;
        })->first();

        return $type;
    }

    public function filesystem($disk = null)
    {
        $disk = $disk ?: config('laradmin.storage.data_disk');

        return Storage::disk($disk);
    }

    public function model($name)
    {
        return app($this->models[studly_case($name)]);
    }

    public function modelClass($name)
    {
        return $this->models[$name];
    }


    public function initJsObject()
    {
        $this->jsObject = [
            'api_base' => route('laradmin.dashboard', [], false),
            'routs' => [
                'icons' => route('laradmin.icons', [], false)
            ],
            'mixins' => []
        ];

        return $this;
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

    protected function registerFormPanels()
    {
        $types = [
            'simple',
            'main_meta'
        ];

        foreach ($types as $type) {
            $class = studly_case("{$type}_Panel");

            $this->manager('formPanels')
                ->register("Shemi\\Laradmin\\FormPanels\\{$class}");
        }

        event('laradmin::form.panels.registered');

        return $this;
    }

    protected function registerFormFields()
    {
        $types = [
            'input',
            'select_multiple',
            'checkboxes',
            'repeater',
            'switch',
            'files',
            'date',
            'select',
            'message',
            'relationship',
            'image',
            'file',
            'tags',
            'time'
        ];

        foreach ($types as $type) {
            $class = studly_case("{$type}_Field");

            $this->manager('formFields')
                ->register("Shemi\\Laradmin\\FormFields\\{$class}");
        }

        event('laradmin::form.fields.registered');

        return $this;
    }

    protected function registerManagers()
    {
        $managers = [
            LinksManager::class,
            FormFieldsManager::class,
            FormPanelsManager::class,
            WidgetsManager::class,
            RolesManager::class
        ];

        foreach ($managers as $manager) {
            $this->registerManager($manager);
        }

        event('laradmin::managers.registered');

        return $this;
    }

    /**
     * @param $manager
     * @return Laradmin
     * @throws InvalidManagerException
     */
    public function registerManager($manager)
    {
        if(is_string($manager)) {
            $manager = app($manager);
        }

        if(! $manager instanceof ManagerContract) {
            throw new InvalidManagerException($manager);
        }

        $this->managers[$manager->getManagerName()] = $manager;

        return $this;
    }

    /**
     * @param $name
     * @return ManagerContract
     * @throws ManagerDoesNotExistsException
     */
    public function manager($name)
    {
        if(! isset($this->managers[$name])) {
            throw new ManagerDoesNotExistsException($name);
        }

        return $this->managers[$name];
    }

    public static function version()
    {
        return static::VERSION;
    }

    public function __call($name, $arguments)
    {
        if(ends_with(strtolower($name), 'link')) {
            return $this->manager('links')->{$name}(...$arguments);
        }

        if(str_contains(strtolower($name), 'formfield')) {
            return $this->manager('formFields')->{$name}(...$arguments);
        }

        if(str_contains(strtolower($name), 'widget')) {
            return $this->manager('widgets')->{$name}(...$arguments);
        }

        throw new \BadMethodCallException("The Method {$name} not found");
    }

}