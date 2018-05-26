<?php

namespace Shemi\Laradmin\Managers;

use Shemi\Laradmin\Contracts\FormFieldContract;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;

class FormFieldsManager implements ManagerContract
{
    const DEFAULT_TYPES = [
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
        'time',
        'group'
    ];

    protected $bucket = [];

    public function exists($type)
    {
        return isset($this->bucket[$type]);
    }

    public function all()
    {
        return collect($this->bucket);
    }

    public function allNames()
    {
        return $this->all()->keys();
    }

    public function get($type)
    {
        return $this->bucket[$type];
    }

    public function register($fieldClass)
    {
        if(! ($fieldClass instanceof FormFieldContract)) {
            $fieldClass = app($fieldClass);
        }

        $this->bucket[$fieldClass->getCodename()] = $fieldClass;

        return $this;
    }

    public function getManagerName()
    {
        return 'formFields';
    }
}