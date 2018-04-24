<?php

namespace Shemi\Laradmin\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $path = $request->session()->pull('url.intended', route('laradmin.dashboard'));

        return $this->response([
            'redirect' => $path
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard(config('laradmin.guard'));
    }

}