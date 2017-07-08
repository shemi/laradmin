<?php

namespace Shemi\Laradmin\RoleSystems;

use Illuminate\Foundation\Auth\User;

class Simple extends RoleSystem
{

    public function getAbilityPrefix()
    {
        return "simple";
    }

}