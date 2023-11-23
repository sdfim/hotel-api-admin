<?php

namespace Modules\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *	title="UJV API Documentation",
 *	version="1.0.0"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     description="authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
 * @OA\Tag(
 *   name="Content API",
 *   description="API Endpoints of Content"
 * ),
 * @OA\Tag(
 *   name="Pricing API",
 *   description="API Endpoints of Price"
 * ),
 * @OA\Tag(
 *   name="Booking API | Cart Endpoints",
 *   description="API Endpoints of Cart (pre-reservation)"
 * ),
 * @OA\Tag(
 *   name="Booking API | Booking Endpoints",
 *   description="API Endpoints of Booking (reservation)"
 * ),
 */

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @param array $result
     * @param string|null $message
     * @return JsonResponse
     */
    public function sendResponse(array $result, ?string $message = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
        ];

        if (empty($response['data'])) unset($response['data']);

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param string $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($error, string $errorMessages = '', int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['message'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
