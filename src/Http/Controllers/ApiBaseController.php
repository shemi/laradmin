<?php

namespace Shemi\Laradmin\Http\Controllers;

class ApiBaseController extends Controller
{
    public function health()
    {
        return $this->response([
            'version' => 'dev',
            'user_status' => \Auth::check()
        ]);
    }
}