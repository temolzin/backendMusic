<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = Auth::guard('api')->setRequest($request)->user();
            if ($user) {
                Auth::setUser($user);
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}
