<?php

namespace Shemi\Laradmin\Exceptions\SyncMedia;

use Exception;
use Shemi\Laradmin\Exceptions\ExceptionContract;
use Throwable;

class SyncMediaException extends Exception implements ExceptionContract
{

    public function __construct(string $message, Throwable $previous = null, int $code = 500)
    {
        $message = $message . ($previous ? ": {$previous->getMessage()}" : "");

        parent::__construct($message, $code, $previous);
    }

}