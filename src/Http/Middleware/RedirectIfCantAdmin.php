<?php

namespace Shemi\Laradmin\Http\Middleware;

use Auth;
use Closure;
use Shemi\Laradmin\Facades\Laradmin;

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
            $user = Laradmin::model('User')->find(Auth::id());

            return $user->can('laradmin.admin.browse') ? $next($request) : redirect('/');
        }

        $urlLogin = route('laradmin.login');
        $urlIntended = $request->url();

        if ($urlIntended == $urlLogin) {
            $urlIntended = null;
        }

        return redirect($urlLogin)->with('url.intended', $urlIntended);
    }

}