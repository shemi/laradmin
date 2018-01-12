<?php

namespace Shemi\Laradmin;

use Illuminate\Support\Facades\Storage;
use Route;
use Shemi\Laradmin\Contracts\FormFieldContract;
use Shemi\Laradmin\Models\User;

class Laradmin
{

    protected $formFields = [];

    protected $models = [
        'User' => User::class
    ];

    protected $jsObject = [];

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

}