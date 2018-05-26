<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;

class CreateUpdateUnableToSaveMediaException extends CreateUpdateException
{

    public static function create($mediaName, $fieldKey, $mediaDisk, Exception $original)
    {
        $message = "Unable to save media \"{$mediaName}\" field key \"{$fieldKey}\" disk \"{$mediaDisk}\"";

        return new static($message, $original);
    }

}