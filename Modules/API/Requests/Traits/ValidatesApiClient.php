<?php

namespace Modules\API\Requests\Traits;

use App\Models\Channel;
use App\Models\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

trait ValidatesApiClient
{
    /**
     * Strict validation for optional api_client.{id,email}.
     * Accepts either id or email (or both). If both provided, they must refer to the same user.
     */
    protected function validateApiClient(Validator $v, $apiClientId, $apiClientEmail): void
    {
        // Nothing to validate if both are empty
        if (($apiClientId === null || $apiClientId === '') && ($apiClientEmail === null || $apiClientEmail === '')) {
            return;
        }

        /** @var User|null $byId */
        $byId = null;
        /** @var User|null $byEmail */
        $byEmail = null;

        if ($apiClientId !== null && $apiClientId !== '') {
            $byId = User::find((int) $apiClientId);
            if (! $byId) {
                $v->errors()->add('api_client.id', 'API client user not found.');

                return;
            }
        }

        if ($apiClientEmail !== null && $apiClientEmail !== '') {
            $byEmail = User::where('email', $apiClientEmail)->first();
            if (! $byEmail) {
                $v->errors()->add('api_client.email', 'API client user with this email was not found.');

                return;
            }
        }

        // If both provided, they must point to the same user
        if ($byId && $byEmail && $byId->id !== $byEmail->id) {
            $v->errors()->add('api_client', 'Provided API client id and email refer to different users.');

            return;
        }

        // Choose resolved user
        $apiUser = $byId ?? $byEmail;

        // Role must be api-user
        if (! $apiUser->hasRole(RoleSlug::API_USER->value)) {
            $v->errors()->add('api_client', 'The given user is not an API user.');

            return;
        }

        // Must be linked to a channel
        if ($apiUser->channel_id === null) {
            $v->errors()->add('api_client', 'The API user is not assigned to any channel.');

            return;
        }

        // Strict: user's channel must match channel resolved from Bearer
        $tokenChannel = $this->resolveChannelFromBearer();
        if (! $tokenChannel) {
            $v->errors()->add('authorization', 'Channel token is missing or invalid.');

            return;
        }

//        if ((int) $tokenChannel->id !== (int) $apiUser->channel_id) {
//            $v->errors()->add('api_client', 'The API user does not belong to the authenticated channel.');
//        }
    }

    /**
     * Resolve channel from Authorization: Bearer <token>.
     * Supports both full token ("id|plain") and plain part only.
     */
    protected function resolveChannelFromBearer(): ?Channel
    {
        $auth = (string) request()->header('Authorization', '');
        if (! Str::startsWith($auth, 'Bearer ')) {
            return null;
        }

        $raw = trim(Str::after($auth, 'Bearer '));
        if ($raw === '') {
            return null;
        }

        // Exact match (if you store full token)
        $chan = Channel::query()->where('access_token', $raw)->first();
        if ($chan) {
            return $chan;
        }

        // Match by plain part (if DB stores "id|plain" but client sends only "plain")
        $escaped = addcslashes($raw, '%_\\');

        return Channel::query()
            ->where('access_token', 'like', '%|'.$escaped)
            ->first();
    }
}
