<?php

namespace Shemi\Laradmin\Tests\Controller\Fakes;

use Shemi\Laradmin\Models\User;

class TestUser extends User
{
    protected $table = 'users';

    protected $guarded = [];

}