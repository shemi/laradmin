<?php

namespace Shemi\Laradmin\RoleSystems;

use Illuminate\Support\Facades\Gate;

abstract class RoleSystem
{
    protected $knownGates = [
        'admin@browse',
        'data@browse',
        'data@create',
    ];

    public function registerPolicies()
    {
        foreach ($this->knownGates as $gate) {
            list($class, $ability) = explode('@', $gate);
            $fullClassName = studly_case("{$class}_Policy");

            Gate::define("laradmin.{$class}.{$ability}", "\\Shemi\\Laradmin\\Policies\\$fullClassName@$ability");
        }

    }

    /**
     * @return string
     */
    public abstract function getAbilityPrefix();

}