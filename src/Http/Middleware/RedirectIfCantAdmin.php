<?php

namespace Shemi\Laradmin\Http\Middleware;

use Auth;
use Closure;

class RedirectIfCantAdmin
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! Auth::guest()) {
            $user = Auth::user();

            return $user->can('laradmin.admin.brows') ? $next($request) : redirect('/');
        }

        $urlLogin = route('laradmin.login');
        $urlIntended = $request->url();

        if ($urlIntended == $urlLogin) {
            $urlIntended = null;
        }

        return redirect($urlLogin)->with('url.intended', $urlIntended);
    }

}