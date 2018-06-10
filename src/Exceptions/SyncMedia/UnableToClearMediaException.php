<?php

namespace Shemi\Laradmin\Exceptions\SyncMedia;

use Exception;

class UnableToClearMediaException extends SyncMediaException
{

    public static function create(Exception $original)
    {
        return new static("Unable to clear media", $original);
    }

}