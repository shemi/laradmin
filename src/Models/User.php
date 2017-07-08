<?php

namespace Shemi\Laradmin\Models;

use Illuminate\Foundation\Auth\User as AuthUser;
use Shemi\Laradmin\Traits\LaradminUser;

class User extends AuthUser
{
    use LaradminUser;


}