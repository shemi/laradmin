<?php

namespace Shemi\Laradmin\Exceptions\SyncMedia;

use Exception;

class MediaModelNotFoundException extends SyncMediaException
{

    public static function create(Exception $original = null)
    {
        return new static("Media model could not be found", $original);
    }

}