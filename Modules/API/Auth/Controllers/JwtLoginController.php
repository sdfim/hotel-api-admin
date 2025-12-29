<?php

namespace Modules\API\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Filament\Notifications\Notification;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Modules\API\Requests\JwtLoginRequest;

class JwtLoginController extends Controller
{
    public function login(JwtLoginRequest $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token not provided.'], 400);
        }

        try {
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        $claims = (array) $decoded;

        if (! isset($claims['email'], $claims['sub'], $claims['first_name'], $claims['last_name'], $claims['exp'])) {
            return response()->json(['message' => 'Missing required claims.'], 400);
        }

        $user = User::withTrashed()->firstOrCreate(
            [
                'email' => $claims['email'],
            ],
            [
                'name' => $claims['first_name'].' '.$claims['last_name'],
                'password' => Hash::make(strtok($claims['email'], '@').'-'.$claims['sub']),
            ]
        );

        if ($user->trashed()) {
            return response()->json(['message' => 'User is soft deleted.'], 403);
        }

        if (! $user->hasRole(RoleSlug::API_USER->value)) {
            $apiUserRoleId = Role::where('slug', RoleSlug::API_USER->value)->value('id');
            $user->roles()->sync([$apiUserRoleId]);

            // Create channel
            $channel = Channel::create([
                'name' => $user->name.' Channel',
                'description' => $user->email.' Channel created on JWT login',
                'user_id' => $user->id,
            ]);

            // Issue token
            $token = $channel->createToken($user->name.' Channel');
            $channel->update([
                'token_id' => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
            ]);

            $user->update(['channel_id' => $channel->id]);

            Notification::make()
                ->title('JWT. New User Created')
                ->body('A new user '.$user->email.' was created via JWT login. sub='.' '.$claims['sub'])
                ->success()
                ->sendToDatabase($user);
        }

        $channel = $user->channel; // Assuming belongsTo(Channel::class)
        if (! $channel || (method_exists($channel, 'trashed') && $channel->trashed())) {
            return response()->json(['message' => 'No active channel assigned.'], 403);
        }

        Notification::make()
            ->title('JWT. Login')
            ->body('User '.$user->email.' logged in via JWT.sub='.' '.$claims['sub'])
            ->success()
            ->sendToDatabase($user);

        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'token' => $channel->plain_access_token,
        ]);
    }
}
