<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

/**
 * @OA\Post(
 *   tags={"Auth API"},
 *   path="/api/auth/generate-external-jwt",
 *   summary="Generate external JWT for a user",
 *   description="Accepts a Bearer token in the Authorization header and a user_id in the body, validates them, and returns a JWT and link.",
 *   security={{"apiAuth":{}}},
 *
 *   @OA\RequestBody(
 *     required=true,
 *
 *     @OA\JsonContent(
 *       required={"user_id"},
 *
 *       @OA\Property(property="user_id", type="integer", example=13)
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=200,
 *     description="OK",
 *
 *     @OA\JsonContent(
 *       type="object",
 *       required={"jwt","link"},
 *
 *       @OA\Property(property="jwt", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *       @OA\Property(property="link", type="string", example="https://vidanta.thoughtindustries.com/access/jwt/?jwt=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=400,
 *     description="Invalid or missing token or user_id",
 *
 *     @OA\JsonContent(
 *       type="object",
 *       required={"message"},
 *
 *       @OA\Property(property="message", type="string", example="Token not provided.")
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=404,
 *     description="User not found",
 *
 *     @OA\JsonContent(
 *       type="object",
 *       required={"message"},
 *
 *       @OA\Property(property="message", type="string", example="User not found.")
 *     )
 *   )
 * )
 */
class JwtGenerateExternalRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'Authorization' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! str_starts_with($value, 'Bearer ')) {
                    $fail('The Authorization header must start with "Bearer ".');
                }
            }],
            'user_id' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'Authorization' => $this->header('Authorization'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function getUserId(): int
    {
        return (int) $this->input('user_id');
    }

    public function getBearerToken(): ?string
    {
        $header = $this->header('Authorization');

        return $header && str_starts_with($header, 'Bearer ')
            ? substr($header, 7)
            : null;
    }
}
