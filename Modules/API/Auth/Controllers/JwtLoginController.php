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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Modules\API\Requests\JwtGenerateExternalRequest;
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

        if (! isset($claims['email'], $claims['externalCustomerId'], $claims['firstName'], $claims['lastName'], $claims['exp'], $claims['iat'])) {
            return response()->json(['message' => 'Missing required claims.'], 400);
        }

        $user = User::withTrashed()->firstOrCreate(
            [
                'email' => $claims['email'],
            ],
            [
                'name' => $claims['firstName'].' '.$claims['lastName'],
                'external_customer_id' => $claims['externalCustomerId'],
                'password' => Hash::make(strtok($claims['email'], '@').'-'.$claims['externalCustomerId']),
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
                ->body('A new user '.$user->email.' was created via JWT login. externalCustomerId='.' '.$claims['externalCustomerId'])
                ->success()
                ->sendToDatabase($user);
        }

        $channel = $user->channel; // Assuming belongsTo(Channel::class)
        if (! $channel || (method_exists($channel, 'trashed') && $channel->trashed())) {
            return response()->json(['message' => 'No active channel assigned.'], 403);
        }

        Notification::make()
            ->title('JWT. Login')
            ->body('User '.$user->email.' logged in via JWT.externalCustomerId='.' '.$claims['externalCustomerId'])
            ->success()
            ->sendToDatabase($user);

        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'token' => $channel->plain_access_token,
        ]);
    }

    /**
     * Generate external JWT for a user and return token and link.
     * POST /api/jwt/generate-external
     * Body: { "user_id": int }
     * Header: Authorization: Bearer {token}
     */
    public function generateExternalJwt(JwtGenerateExternalRequest $request): JsonResponse
    {
        $userId = $request->getUserId();
        $token = $request->getBearerToken();

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (! $user->hasRole(RoleSlug::API_USER->value)) {
            return response()->json(['message' => 'User is not allowed to access API.'], 403);
        }

        $channel = $user->channel; // belongsTo(Channel::class)
        if (! $channel || (method_exists($channel, 'trashed') && $channel->trashed())) {
            return response()->json(['message' => 'No active channel assigned.'], 403);
        }

        $key = config('jwt.secret_external');

        // Call artisan command to generate JWT
        $email = $user->email;
        $name = explode(' ', $user->name);
        $externalId = $user->external_customer_id;
        $firstName = $name[0];
        $lastName = $name[1] ?? '';

        Artisan::call('jwt:generate', [
            'email' => $email,
            'sub' => $externalId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'key' => $key,
        ]);

        $output = Artisan::output();

        if (empty($output)) {
            return response()->json(['message' => 'Failed to generate JWT.'], 500);
        }

        $jwt = trim($output);
        $link = 'https://vidanta.thoughtindustries.com/access/jwt/?jwt='.urlencode($jwt);

        return response()->json([
            'jwt' => $jwt,
            'link' => $link,
        ]);
    }
}
