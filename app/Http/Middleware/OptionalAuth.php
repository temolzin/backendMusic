<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            try {
                if ($user = JWTAuth::parseToken()->authenticate()) {
                    auth()->setUser($user);
                }
            } catch (JWTException $exception) {
            }
        }

        return $next($request);
    }
}