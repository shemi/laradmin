<?php

namespace Shemi\Laradmin\Managers;

use Shemi\Laradmin\Contracts\FormPanelContract;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;

class FormPanelsManager implements ManagerContract
{
    protected $bucket = [];

    public function typeExists($type)
    {
        return isset($this->bucket[$type]);
    }

    public function all()
    {
        return $this->bucket;
    }

    public function names()
    {
        return collect($this->bucket)
            ->keys();
    }

    public function panel($type)
    {
        return $this->bucket[$type];
    }

    public function register($class)
    {
        if(! ($class instanceof FormPanelContract)) {
            $class = app($class);
        }

        $this->bucket[$class->getCodename()] = $class;

        return $this;
    }

    public function getManagerName()
    {
        return 'formPanels';
    }
}