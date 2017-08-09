<?php

namespace Shemi\Laradmin\Traits;

use Spatie\Permission\Traits\HasRoles;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait LaradminUser
{
    use HasRoles;

}