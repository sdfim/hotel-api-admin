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

        if (in_array($appEnv, ['production', 'prod', 'development', 'dev']) &&
            $request->headers->has('referer') &&
            $request->path() !== "/"
        ) {
            $referer = parse_url($request->headers->get('referer'));
            $scheme = $referer['scheme'];
            $host = $referer['host'];
            $port = isset($referer['port']) ? ':' . $referer['port'] : '';
            $referrerWithoutPath = $scheme . '://' . $host . $port;
            $response->headers->set('Location', $referrerWithoutPath);
        }

        return $response;
    }
}
