<?php

namespace Shemi\Laradmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Route;
use Shemi\Laradmin\Contracts\FormFieldContract;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Models\User;

class Laradmin
{
    const VERSION = "0.6.5";

    protected $formFields = [];

    protected $models = [
        'User' => User::class
    ];

    protected $jsObject = [];

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

    public function formFieldExists($type)
    {
        return isset($this->formFields[$type]);
    }

    public function formFields()
    {
        return $this->formFields;
    }

    public function getFormFieldNames()
    {
        return collect($this->formFields)
            ->keys();
    }

    public function formField($type)
    {
        return $this->formFields[$type];
    }

    public function addFormField($fieldClass)
    {
        if(! ($fieldClass instanceof FormFieldContract)) {
            $fieldClass = app($fieldClass);
        }

        $this->formFields[$fieldClass->getCodename()] = $fieldClass;

        return $this;
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

    public static function version()
    {
        return static::VERSION;
    }

    public function editLink(Type $type, $model = null)
    {
       return $this->typeLink($type, 'edit', $model);
    }

    public function destroyLink(Type $type, $model = null)
    {
       return $this->typeLink($type, 'destroy', $model);
    }

    public function destroyManyLink(Type $type)
    {
       return $this->typeLink($type, 'destroyMany');
    }

    public function typeLink(Type $type, $action, $modelOrKey = null)
    {
        if($modelOrKey === null) {
            return route("laradmin.{$type->slug}.{$action}");
        }

        $link = route("laradmin.{$type->slug}.{$action}", ["{$type->slug}" => "__primaryKey__"]);

        if($modelOrKey && $modelOrKey instanceof Model) {
            return str_replace('__primaryKey__', $modelOrKey->getKey(), $link);
        }

        return str_replace('__primaryKey__', "'+ props.row.{$modelOrKey} +'", $link);
    }

}