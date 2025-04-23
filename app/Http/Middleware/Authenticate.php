<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * @param  Request  $request
     *
     * @throws AuthenticationException
     */
    protected function unauthenticated($request, array $guards): void
    {
        if ($request->is('api/*')) {
            abort(response()->json(
                [
                    'api_status' => '401',
                    'message' => 'UnAuthenticated',
                ], 401));
        } else {
            redirect()->route('root')->send();
        }
    }
}
