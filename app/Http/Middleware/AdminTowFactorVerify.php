<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminTowFactorVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (authAdmin() && authAdmin()->google2fa_status && !$request->session()->has('admin_2fa') &&
            session('admin_2fa') != hashId(authAdmin()->id)) {
            return redirect()->route('admin.2fa.verify');
        }

        return $next($request);
    }
}
