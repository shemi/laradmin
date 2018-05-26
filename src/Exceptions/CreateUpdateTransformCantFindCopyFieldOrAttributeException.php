<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;

class CreateUpdateTransformCantFindCopyFieldOrAttributeException extends CreateUpdateException
{

    public static function create($copyKey, Exception $original = null)
    {
        $message = "Cant find transform field or attribute with the name \"{$copyKey}\"";

        return new static($message, $original);
    }

}