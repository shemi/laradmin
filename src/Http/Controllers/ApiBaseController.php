<?php

namespace Shemi\Laradmin\Http\Controllers;

use Shemi\Laradmin\Laradmin;

class ApiBaseController extends Controller
{
    public function health()
    {
        return $this->response([
            'version' => Laradmin::version(),
            'user_status' => \Auth::check()
        ]);
    }
}