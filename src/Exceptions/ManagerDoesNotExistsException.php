<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;
use Throwable;

class ManagerDoesNotExistsException extends Exception implements ExceptionContract
{

    public function __construct($name, Throwable $previous = null, $code = 0)
    {
        $message = "The manager \"{$name}\" does not exist";

        parent::__construct($message, $code, $previous);
    }

}