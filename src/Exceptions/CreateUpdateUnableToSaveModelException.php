<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;

class CreateUpdateUnableToSaveModelException extends CreateUpdateException
{

    public static function create($modelClass, Exception $original)
    {
        $message = "Unable to save model \"{$modelClass}\"";

        return new static($message, $original);
    }

}