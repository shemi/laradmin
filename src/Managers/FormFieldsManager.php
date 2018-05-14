<?php

namespace Shemi\Laradmin\Managers;

use Shemi\Laradmin\Contracts\FormFieldContract;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;

class FormFieldsManager implements ManagerContract
{
    protected $bucket = [];

    public function formFieldExists($type)
    {
        return isset($this->bucket[$type]);
    }

    public function formFields()
    {
        return $this->bucket;
    }

    public function getFormFieldNames()
    {
        return collect($this->bucket)
            ->keys();
    }

    public function formField($type)
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