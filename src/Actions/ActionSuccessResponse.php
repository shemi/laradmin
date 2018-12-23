<?php

namespace Shemi\Laradmin\Actions;


use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ActionSuccessResponse extends ActionResponse
{

    public static function make($message = "")
    {
        return (new static($message, static::TYPE_SUCCESS));
    }

    protected function getMessage(Request $request)
    {
        if(! $this->message) {
            return "The action finish successfully!";
        }

        return $this->message;
    }

}