<?php

namespace Shemi\Laradmin\Exceptions;

use Exception;
use Shemi\Laradmin\Contracts\Managers\ManagerContract;

class InvalidManagerException extends Exception implements ExceptionContract
{

    public function __construct($manager, \Exception $previous = null, $code = 0)
    {
        $name = is_string($manager) ? $manager : get_class($manager);

        $contract = ManagerContract::class;

        $message = "The manager \"{$name}\" most implement the \"{$contract}\" contract";

        parent::__construct($message, $previous, $code);
    }

}