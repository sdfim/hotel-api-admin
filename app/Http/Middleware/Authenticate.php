<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;


class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo (Request $request): ?string
    {
		if (! $request->expectsJson()) {
            return route('login');
        }
    }

	/**
	 * @param Request $request
	 * @param array $guards
	 * @return void
	 */
	protected function unauthenticated($request, array $guards) : void
    {
        abort(response()->json(
            [
                'api_status' => '401',
                'message' => 'UnAuthenticated',
            ], 401));
    }
}
