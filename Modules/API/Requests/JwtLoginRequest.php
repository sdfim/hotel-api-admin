<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class JwtLoginRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Auth API"},
     *   path="/api/auth/jwt-login",
     *   summary="Authenticate using a JWT token",
     *   description="Accepts a JWT token in the Authorization header, validates it, and returns user details along with a channel token.",
     *
     *   security={{"ssoJwtAuth":{}}},
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       required={"user_id","name","token"},
     *       @OA\Property(property="user_id", type="integer", example=18),
     *       @OA\Property(property="name", type="string", example="test-api-user"),
     *       @OA\Property(property="token", type="string", example="Rx1rkXk0S9MomMpssi5D9T4kfUI5MwHxRZDSwYoHecbb4ace"),
     *       example={"user_id":18,"name":"test-api-user","token":"Rx1rkXk0S9MomMpssi5D9T4kfUI5MwHxRZDSwYoHecbb4ace"}
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Invalid or missing token",
     *     @OA\JsonContent(
     *       type="object",
     *       required={"message"},
     *       @OA\Property(property="message", type="string", example="Token not provided or invalid.")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @OA\JsonContent(
     *       type="object",
     *       required={"message"},
     *       @OA\Property(property="message", type="string", example="Invalid token.")
     *     )
     *   )
     * )
     */
    public function rules(): array
    {
        return [
            'Authorization' => ['required', 'string', function ($attribute, $value, $fail) {
                if (! str_starts_with($value, 'Bearer ')) {
                    $fail('The Authorization header must start with "Bearer ".');
                }
            }],
        ];
    }

    /**
     * Prepare the data for validation.
     * * @return void
     */
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
}
