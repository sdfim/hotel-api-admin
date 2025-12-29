<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class ChannelTokenRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Auth API"},
     *   path="/api/auth/channel-token",
     *   summary="Issue a channel token for an API user bound to a channel",
     *   description="Authenticates by email/password, requires 'api-user' role and an active channel assignment. Returns channel token.",
     *   security={},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\MediaType(
     *       mediaType="application/json",
     *
     *       @OA\Schema(
     *         type="object",
     *         required={"email","password"},
     *
     *         @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         @OA\Property(property="password", type="string", format="password", example="secret")
     *       ),
     *
     *       @OA\Examples(
     *         example="Example Channel Token Request",
     *         summary="Minimal login payload",
     *         value={"email"="user@example.com","password"="secret"}
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       type="object",
     *       required={"user_id","name","token"},
     *
     *       @OA\Property(property="user_id", type="integer", example=18),
     *       @OA\Property(property="name", type="string", example="test-api-user"),
     *       @OA\Property(property="token", type="string", example="Rx1rkXk0S9MomMpssi5D9T4kfUI5MwHxRZDSwYoHecbb4ace"),
     *
     *       example={"user_id":18,"name":"test-api-user","token":"Rx1rkXk0S9MomMpssi5D9T4kfUI5MwHxRZDSwYoHecbb4ace"}
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Invalid credentials",
     *
     *     @OA\JsonContent(type="object", required={"message"}, @OA\Property(property="message", type="string", example="Invalid credentials."))
     *   ),
     *
     *   @OA\Response(
     *     response=403,
     *     description="User is not allowed or no active channel",
     *
     *     @OA\JsonContent(
     *       oneOf={
     *
     *         @OA\Schema(type="object", required={"message"}, @OA\Property(property="message", type="string", example="User is not allowed to access API.")),
     *         @OA\Schema(type="object", required={"message"}, @OA\Property(property="message", type="string", example="No active channel assigned."))
     *       }
     *     )
     *   )
     * )
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
