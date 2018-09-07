<?php

namespace Shemi\Laradmin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Route;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;
use Shemi\Laradmin\Exceptions\InvalidManagerException;
use Shemi\Laradmin\Exceptions\ManagerDoesNotExistsException;
use Shemi\Laradmin\Managers\DynamicsManager;
use Shemi\Laradmin\Managers\FormFieldsManager;
use Shemi\Laradmin\Managers\FormPanelsManager;
use Shemi\Laradmin\Managers\JsVarsManager;
use Shemi\Laradmin\Managers\LinksManager;
use Shemi\Laradmin\Managers\RolesManager;
use Shemi\Laradmin\Managers\WidgetsManager;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Models\User;

/**
 * Class Laradmin
 * @package Shemi\Laradmin
 *
 * @method LinksManager links
 * @method WidgetsManager widgets
 * @method FormFieldsManager formFields
 * @method FormPanelsManager formPanels
 * @method JsVarsManager jsVars
 * @method RolesManager roles
 * @method DynamicsManager dynamics
 *
 */

class Laradmin
{
    const VERSION = "0.7.2";

    protected $managers = [];

    public function init()
    {
        $this->registerManagers()
            ->registerFormPanels()
            ->registerFormFields();
    }

    public function user()
    {
        return Auth::guard(config('laradmin.guard'))
            ->user();
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

    protected function registerFormPanels()
    {
        $types = FormPanelsManager::DEFAULT_TYPES;

        foreach ($types as $type) {
            $class = studly_case("{$type}_Panel");

            $this->formPanels()
                ->register("Shemi\\Laradmin\\FormPanels\\{$class}");
        }

        event('laradmin::form.panels.registered');

        return $this;
    }

    protected function registerFormFields()
    {
        $types = FormFieldsManager::DEFAULT_TYPES;

        foreach ($types as $type) {
            $class = studly_case("{$type}_Field");

            $this->formFields()
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
            RolesManager::class,
            JsVarsManager::class,
            DynamicsManager::class,
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

    public function managerExists($name)
    {
        return isset($this->managers[$name]);
    }

    /**
     * @param $name
     * @return ManagerContract
     * @throws ManagerDoesNotExistsException
     */
    public function manager($name)
    {
        if(! $this->managerExists($name)) {
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
        if($this->managerExists($name)) {
            return $this->manager($name);
        }

        throw new \BadMethodCallException("The Method {$name} not found");
    }

}