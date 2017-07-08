<?php

namespace Shemi\Laradmin\Data;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DataNotFoundException extends NotFoundHttpException
{

    public function __construct($name, \Exception $previous = null, $code = 0)
    {
        $message = "The data \"{$name}\" not found.";

        parent::__construct($message, $previous, $code);
    }

}