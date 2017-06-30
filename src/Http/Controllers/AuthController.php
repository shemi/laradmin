<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function showLoginForm()
    {
        return view('laradmin::auth.login');
    }

}