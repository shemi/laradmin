<?php

namespace Shemi\Laradmin\Policies;

use Shemi\Laradmin\Facades\Laradmin;

class Policy
{
    protected $superusers = [];

    public function __construct()
    {
        $this->superusers = config('laradmin.superusers');
    }

    /**
     * @param $user
     * @param $ability
     * @return bool
     */
    public function before($user, $ability)
    {
        if(in_array($user->email, $this->superusers)) {
            return true;
        }

        return false;
    }

    public function __call($name, $arguments)
    {
        if($this->before($arguments[0], $name)) {
            return true;
        }

        $prefix = Laradmin::getRoleSystem()->getAbilityPrefix();
        $prefixedMethod = camel_case("{$prefix}_{$name}");

        if(method_exists($this, $prefixedMethod)) {
            return call_user_func_array([$this, $prefixedMethod], $arguments);
        }

        return call_user_func_array([$this, $name], $arguments);
    }

}