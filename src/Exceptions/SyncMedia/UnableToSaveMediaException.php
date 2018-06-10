<?php

namespace Shemi\Laradmin\Exceptions\SyncMedia;

use Exception;

class UnableToSaveMediaException extends SyncMediaException
{

    public static function create($mediaName, $fieldKey, $mediaDisk, Exception $original)
    {
        $message = "Unable to save media \"{$mediaName}\" field key \"{$fieldKey}\" disk \"{$mediaDisk}\"";

        return new static($message, $original);
    }

}