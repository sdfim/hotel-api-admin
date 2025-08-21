<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class ChannelTokenRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Auth API | Channel Clients"},
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
     *       required={"user_id","token"},
     *
     *       @OA\Property(property="user_id", type="integer", example=42),
     *       @OA\Property(property="token", type="string", example="p1a2r3t4OfPlainAccessToken")
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
