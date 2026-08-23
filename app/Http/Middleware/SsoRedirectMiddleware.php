<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Clear session residue if visiting login page directly without SSO request
        if (($request->is('login') || $request->is('admin/login') || $request->is('register') || $request->is('admin/register')) && !$request->has('sso')) {
            session()->forget('sso_redirect');
        }

        if (Auth::check() && session()->has('sso_redirect')) {
            $redirect = session()->pull('sso_redirect');
            return redirect()->route('sso.login', ['redirect' => $redirect]);
        }

        return $next($request);
    }
}
