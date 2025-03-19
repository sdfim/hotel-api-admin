<?php

namespace App\Http\Middleware;

use App\Models\Channel;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckChannelNotDeleted
{
    /**
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->header('Authorization');

        if (str_starts_with($accessToken, 'Bearer ')) {
            $accessToken = substr($accessToken, 7);
        }

        $channel = Channel::withTrashed()->where('access_token', 'like', '%'.$accessToken)->first();

        if ($channel && $channel->trashed()) {
            return response()->json(['error' => 'Channel is deleted'], 403);
        }

        return $next($request);
    }
}
