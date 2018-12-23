<?php

namespace Shemi\Laradmin\Actions;


use Illuminate\Http\Request;

class ActionWarningResponse extends ActionResponse
{

    public static function make($message = "")
    {
        return (new static($message, static::TYPE_WARNING));
    }

    protected function getMessage(Request $request)
    {
        if(! $this->message) {
            return "The action finished with with errors.";
        }

        return $this->message;
    }

}