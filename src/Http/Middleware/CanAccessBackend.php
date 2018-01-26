<?php

namespace Shemi\Laradmin\Http\Middleware;

use Auth;
use Closure;

class CanAccessBackend
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $guard = $guard ?: config('laradmin.guard');

        if (Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();

            return $user->can('access backend') ? $next($request) : redirect('/');
        }


        $urlLogin = route('laradmin.login');
        $urlIntended = $request->url();

        if ($urlIntended == $urlLogin) {
            $urlIntended = null;
        }

        return redirect($urlLogin)->with('url.intended', $urlIntended);
    }

}