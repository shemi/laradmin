<?php

namespace Shemi\Laradmin\Exceptions\SyncMedia;

use Exception;

class CantDeleteMediaException extends SyncMediaException
{

    /**
     * @param $mediaId
     * @param $collection
     * @param Exception $original
     * @return static
     */
    public static function create($mediaId, $collection, Exception $original)
    {
        $message = "Unable to delete media with id: {$mediaId} collection: \"{$collection}\"";

        return new static($message, $original);
    }

}