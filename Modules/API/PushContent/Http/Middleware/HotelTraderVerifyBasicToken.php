<?php

namespace Modules\API\PushContent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HotelTraderVerifyBasicToken
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');
        if (! $authHeader || ! str_starts_with($authHeader, 'Basic ')) {
            return response()->json(['message' => 'Missing or invalid Authorization header'], 401);
        }

        $token = substr($authHeader, 6);
        $decoded = base64_decode($token, true);
        if (! $decoded || ! str_contains($decoded, ':')) {
            return response()->json(['message' => 'Invalid Basic token'], 401);
        }

        [$username, $password] = explode(':', $decoded, 2);

        $expectedUsername = config('booking-suppliers.HotelTrader.push_credentials.username');
        $expectedPassword = config('booking-suppliers.HotelTrader.push_credentials.password');

        if ($username !== $expectedUsername || $password !== $expectedPassword) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $next($request);
    }
}
