<?php

namespace Shemi\Laradmin\Managers;

use Shemi\Laradmin\Contracts\FormFieldContract;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;
use Shemi\Laradmin\Filters\Filter;

class FiltersManager implements ManagerContract
{
    protected $bucket = [];

    public function has($name)
    {
        return isset($this->bucket[$name]);
    }

    public function all()
    {
        return collect($this->bucket);
    }

    public function allNames()
    {
        return $this->all()->keys();
    }

    public function get($name)
    {
        return $this->bucket[$name];
    }

    public function register($class)
    {
        if(! ($class instanceof Filter)) {
            $class = app($class);
        }

        $this->bucket[$class->getName()] = $class;

        return $this;
    }

    public function getManagerName()
    {
        return 'filters';
    }
}