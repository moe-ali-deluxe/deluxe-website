<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class AdminMiddleware
{
   public function handle(Request $request, Closure $next)
{
 \Log::info('✅ AdminMiddleware triggered', ['user' => Auth::user()?->email]);
    if (Auth::check() && Auth::user()->is_admin) {
        return $next($request);
    }

    abort(403, 'Unauthorized: Admins only.');
}
}
