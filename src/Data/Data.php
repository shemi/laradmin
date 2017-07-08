<?php

namespace Shemi\Laradmin\Data;

class Data
{
    public static function location($name)
    {
        return (new static)->newManger()->location($name);
    }

    /**
     * @return DataManager
     */
    public function newManger()
    {
        return new DataManager;
    }

    public function __call($name, $arguments)
    {
        return $this->newManger()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->$name(...$arguments);
    }

}