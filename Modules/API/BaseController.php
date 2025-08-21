<?php

namespace Modules\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;

/**
 * @OA\Info(
 *    title="TerraMare Main API Documentation",
 *    version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
 *
 * @OA\Tag(
 *   name="Content API",
 *   description="API Endpoints of Content"
 * ),
 * @OA\Tag(
 *   name="Pricing API",
 *   description="API Endpoints of Price"
 * ),
 * @OA\Tag(
 *   name="Booking API | Basket",
 *   description="API Endpoints of Cart (pre-reservation)"
 * ),
 * @OA\Tag(
 *   name="Booking API | Booking",
 *   description="API Endpoints of Booking (reservation)"
 * ),
 * @OA\Tag(
 *   name="Booking API | Change Booking",
 *   description="API Endpoints of Booking Changes (modification)"
 * ),
 * @OA\Tag(
 *   name="Auth API | Channel Clients",
 *   description="Issue channel tokens for API clients. Authenticates user by email/password, requires 'api-user' role and an active bound channel, returns channel token for subsequent requests."
 * )
 */
class BaseController extends Controller
{
    /**
     * success response method.
     */
    public function sendResponse(array $result, ?string $message = null, ?int $code = 200, ?bool $stream = false): JsonResponse|StreamedJsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
        ];

        if (empty($response['data'])) {
            unset($response['data']);
        }

        if ($message) {
            $response['message'] = $message;
        }

        if ($stream) {
            return response()->streamJson($response, $code);
        }

        return response()->json($response, $code);
    }

    /**
     * return error response.
     */
    public function sendError($error, string $errorMessages = '', int $code = 400, array $data = []): JsonResponse
    {
        $response = [
            'data' => $data,
            'success' => false,
            'error' => $error,
        ];

        if (! empty($errorMessages)) {
            $response['message'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    protected function filter($query, $model)
    {
        $filterableFields = $model::getFilterableFields();

        foreach ($filterableFields as $field) {
            if (request()->has($field)) {
                if (str_ends_with($field, '_id')) {
                    $query->where($field, request()->input($field)); // Exact match for *_id fields
                } else {
                    $query->where($field, 'like', '%'.request()->input($field).'%'); // Partial match for other fields
                }
            }
        }

        return $query;
    }
}
