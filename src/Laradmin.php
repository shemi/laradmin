<?php

namespace Shemi\Laradmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Route;
use Shemi\Laradmin\Contracts\FormFieldContract;
use Shemi\Laradmin\Exceptions\InvalidArgumentException;
use Shemi\Laradmin\Models\Type;
use Shemi\Laradmin\Models\User;
use Shemi\Laradmin\Widgets\Widget;

class Laradmin
{
    const VERSION = "0.6.5";

    protected $formFields = [];

    protected $widgets = [];

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

    public function registerWidgetsRow($widgets)
    {
        $row = count($this->widgets);

        if(! isset($this->widgets[$row]) || ! is_array($this->widgets[$row])) {
            $this->widgets[$row] = [];
        }

        foreach ($widgets as $widget) {
            $this->registerWidget($widget, $row);
        }

        return $this;
    }

    public function registerWidget($widgetClass, $row = 0)
    {
        if(is_string($widgetClass)) {
            $widgetClass = $widgetClass::start();
        }

        if(! $widgetClass instanceof Widget) {
            throw new InvalidArgumentException("All widgets most extent " . Widget::class);
        }

        if($widgetClass->getSize() > Widget::MAX_WIDGETS_WIDTH_SIZE_PER_ROW) {
            throw new InvalidArgumentException("The widget width cannot be greater than " . Widget::MAX_WIDGETS_WIDTH_SIZE_PER_ROW);
        }

        if(! isset($this->widgets[$row]) || ! is_array($this->widgets[$row])) {
            $this->widgets[$row] = [];
        }

        $rowCount = $this->getWidgetsRowTotal($row);

        if($rowCount + $widgetClass->getSize() > Widget::MAX_WIDGETS_WIDTH_SIZE_PER_ROW) {
            return $this->registerWidget($widgetClass, $row + 1);
        }

        $this->widgets[$row][$widgetClass->getCodename()] = $widgetClass;

        return $this;
    }

    protected function getWidgetsRowTotal($row)
    {
        $count = 0;

        if(! isset($this->widgets[$row]) || empty($this->widgets[$row])) {
            return $count;
        }

        /** @var Widget $widget */
        foreach ($this->widgets[$row] as $widget) {
            $count += $widget->getSize();
        }

        return $count;
    }

    public function widgetsRows()
    {
        return $this->widgets;
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

        if($modelOrKey && is_int($modelOrKey)) {
            return str_replace('__primaryKey__', $modelOrKey, $link);
        }

        return str_replace('__primaryKey__', "'+ props.row.{$modelOrKey} +'", $link);
    }

}