<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;

class CreateUpdateUnableToClearMediaException extends CreateUpdateException
{

    public static function create(Exception $original)
    {
        return new static("Unable to clear media", $original);
    }

}