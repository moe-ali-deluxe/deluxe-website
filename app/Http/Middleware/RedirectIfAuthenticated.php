<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): RedirectResponse
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect('/home'); // Change to your desired redirect
            }
        }

        return $next($request);
    }
}
