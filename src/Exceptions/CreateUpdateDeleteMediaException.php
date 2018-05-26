<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;

class CreateUpdateDeleteMediaException extends CreateUpdateException
{

    /**
     * @param $mediaId
     * @param $fieldKey
     * @param Exception $original
     * @return static
     */
    public static function create($mediaId, $fieldKey, Exception $original)
    {
        $message = "Unable to delete media with id: {$mediaId} collection: \"{$fieldKey}\"";

        return new static($message, $original);
    }

}