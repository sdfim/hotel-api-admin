<?php

namespace Modules\API\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChannelAuthController extends Controller
{
    /**
     * POST /api/auth/channel-token
     * Body: { "email": "user@example.com", "password": "secret" }
     * Returns: JSON with channel token if user is api-user and bound to a channel.
     */
    public function issueChannelToken(Request $request): JsonResponse
    {
        // Validate input
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Find user by email
        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        // Fail fast on wrong credentials (generic message to avoid user enumeration)
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Must have api-user role
        if (! $user->hasRole(RoleSlug::API_USER->value)) {
            return response()->json(['message' => 'User is not allowed to access API.'], 403);
        }

        // Must be bound to an active channel
        $channel = $user->channel; // belongsTo(Channel::class)
        if (! $channel || method_exists($channel, 'trashed') && $channel->trashed()) {
            return response()->json(['message' => 'No active channel assigned.'], 403);
        }

        // Return channel token
        return response()->json([
            'channel_id' => $channel->id,
            'token' => $channel->plain_access_token, // the part after "|"
        ]);
    }
}
