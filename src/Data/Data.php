<?php

namespace Shemi\Laradmin\Data;

use Illuminate\Support\Collection;
use Shemi\Laradmin\Facades\Laradmin;


class Data
{
    protected $name;

    protected $location;

    protected $data;

    public function __construct($name, $location, $data = [])
    {
        $this->name = $name;
        $this->location = $location;

        $this->setData($data);
    }

    public function setData($data = [])
    {
        $this->data = new Collection($data);

        return $this;
    }

    public function save()
    {
        return Laradmin::data()->save($this);
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __get($key)
    {
        if($this->data->has($key)) {
            return $this->data->get($key);
        }

        return $this->{$key};
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this->data, $name)) {
            $return = $this->data->{$name}(...$arguments);

            if($return instanceof Collection) {
                $this->data = $return;

                return $this;
            }

            return $return;
        }

        return $this->{$name}(...$arguments);
    }

}