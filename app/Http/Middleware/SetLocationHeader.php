<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocationHeader
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $appEnv = env('APP_ENV') ?? config('app.env');
        $appUrl = env('APP_URL') ?? config('app.url');

        if (in_array($appEnv, ['production', 'prod', 'development', 'dev']) && $request->path() !== "/")
            $response->headers->set('Location', $appUrl);

        return $response;
    }
}
