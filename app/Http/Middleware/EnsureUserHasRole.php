<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (empty($roles)) {
            abort(403);
        }

        if (! $user->hasRole(...$roles)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
