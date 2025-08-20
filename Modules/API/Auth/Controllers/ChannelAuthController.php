<?php

namespace Modules\API\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Modules\API\Requests\ChannelTokenRequest;

class ChannelAuthController extends Controller
{
    /**
     * POST /api/auth/channel-token
     * Body: { "email": "user@example.com", "password": "secret" }
     * Returns: JSON with channel token if user is api-user and bound to a channel.
     */
    public function issueChannelToken(ChannelTokenRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->hasRole(RoleSlug::API_USER->value)) {
            return response()->json(['message' => 'User is not allowed to access API.'], 403);
        }

        $channel = $user->channel; // belongsTo(Channel::class)
        if (! $channel || (method_exists($channel, 'trashed') && $channel->trashed())) {
            return response()->json(['message' => 'No active channel assigned.'], 403);
        }

        return response()->json([
            'user_id' => $user->id,
            'token' => $channel->plain_access_token,
        ]);
    }
}
