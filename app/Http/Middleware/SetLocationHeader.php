<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocationHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $appEnv = config('engine.env');
        $path = $request->path();

        if (in_array($appEnv, ['production', 'prod', 'development', 'dev'])) {
            if (($path === 'admin/login' && Auth::check()) ||
                ($path === 'admin/reservations') && Auth::check()) {
                $response->headers->set('Location', config('app.url').'/admin/reservations');
            } elseif (($path === 'admin/login' && ! Auth::check())) {
                $response->headers->set('Location', config('app.url').'/admin/login');
            } elseif ($path === 'admin/logout' && ! Auth::check()) {
                $response->headers->set('Location', config('app.url').'/admin/login');
            } elseif (
                $request->headers->has('referer') && $path !== '/'
            ) {
                $referer = parse_url($request->headers->get('referer'));
                $scheme = $referer['scheme'];
                $host = $referer['host'];
                $port = isset($referer['port']) ? ':'.$referer['port'] : '';
                $referrerWithoutPath = $scheme.'://'.$host.$port;
                $response->headers->set('Location', $referrerWithoutPath);
            }
        }

        return $response;
    }
}
