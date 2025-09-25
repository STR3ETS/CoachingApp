<?php

namespace App\Http\Middleware;

use Closure;

class EnsureUserRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->check() || !in_array(auth()->user()->role, $roles, true)) {
            abort(403);
        }
        return $next($request);
    }
}
