<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class RewriteUrls
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $path = $request->path();
        if (! str_contains($path, 'log-viewer')) {
            URL::forceRootUrl(config('app.url'));
        }

        return $next($request);
    }
}
