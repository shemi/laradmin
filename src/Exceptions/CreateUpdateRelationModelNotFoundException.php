<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;

class CreateUpdateRelationModelNotFoundException extends CreateUpdateException
{

    public static function create($fieldKey, Exception $original = null)
    {
        $message = "the field \"{$fieldKey}\" marked as relationship but does not returned as \"" . Relation::class . "\"";

        return new static($message, $original);
    }

}