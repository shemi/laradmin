<?php

namespace Shemi\Laradmin\Actions;


use Illuminate\Http\File;
use Illuminate\Http\Request;

class ActionRedirectResponse extends ActionResponse
{

    protected $redirectTo;

    public static function make($to, $message = null)
    {
        return (new static($message, static::TYPE_REDIRECT))
            ->setRedirectTo($to);
    }

    public function setRedirectTo($to)
    {
        $this->redirectTo = $to;

        return $this;
    }

    protected function getMessage(Request $request)
    {
        if(! $this->message) {
            return "redirecting...";
        }

        return $this->message;
    }

}