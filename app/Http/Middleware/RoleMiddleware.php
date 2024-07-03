<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  null  $permission
     */
    public function handle(Request $request, Closure $next, $role, $permission = null): mixed
    {
        if (! auth()->user()->hasRole($role)) {
            abort(404);
        }
        if ($permission !== null && ! auth()->user()->can($permission)) {
            abort(404);
        }

        return $next($request);
    }
}
