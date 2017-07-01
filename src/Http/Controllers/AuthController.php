<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function showLoginForm()
    {
        return view('laradmin::auth.login');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = ['form' => [trans('auth.failed')]];

        return response()->json($errors, 422);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        $message = trans('auth.throttle', ['seconds' => $seconds]);
        $errors = ['form' => [$message]];

        return response()->json($errors, 423);
    }

    protected function authenticated(Request $request, $user)
    {
        return $this->response([
            'redirect' => route('laradmin.dashboard')
        ]);
    }

}