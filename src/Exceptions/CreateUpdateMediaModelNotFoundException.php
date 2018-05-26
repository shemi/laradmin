<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;

class CreateUpdateMediaModelNotFoundException extends CreateUpdateException
{

    public static function create(Exception $original = null)
    {
        return new static("Media model could not be found", $original);
    }

}