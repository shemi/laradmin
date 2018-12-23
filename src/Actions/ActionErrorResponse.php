<?php

namespace Shemi\Laradmin\Actions;


use Exception;
use Illuminate\Http\Request;

class ActionErrorResponse extends ActionResponse
{

    public static function make($message = "")
    {
        $code = null;

        if($message instanceof Exception) {
            $code = is_int($message->getCode()) ? $message->getCode() : null;
            $message = $message->getMessage();
        }

        return (new static($message, static::TYPE_ERROR, $code));
    }

    protected function getMessage(Request $request)
    {
        if(! $this->message) {
            return "The action failed to execute!";
        }

        return $this->message;
    }

}