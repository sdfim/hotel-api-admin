<?php

namespace Modules\API\PushContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HotelTraderHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Check Content-Type
        if ($request->header('Content-Type') !== 'application/json') {
            return response()->json(['message' => 'Invalid Content-Type'], 415);
        }

        // Check Accept-Encoding
        if ($request->header('Accept-Encoding') !== 'gzip') {
            return response()->json(['message' => 'Invalid Accept-Encoding'], 406);
        }

        // Check Authorization
        $auth = $request->header('Authorization');
        if (! $auth || ! str_starts_with($auth, 'Basic ')) {
            return response()->json(['message' => 'Missing or invalid Authorization header'], 401);
        }

        // Optionally, decode and validate credentials here

        return $next($request);
    }
}
