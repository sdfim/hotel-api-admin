<?php

namespace Modules\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *    title="UJV API Documentation",
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
 *   name="Booking API | Cart Endpoints",
 *   description="API Endpoints of Cart (pre-reservation)"
 * ),
 * @OA\Tag(
 *   name="Booking API | Booking Endpoints",
 *   description="API Endpoints of Booking (reservation)"
 * ),
 * @OA\Tag(
 *     name="Content Repository | Hotel API",
 *     description="API Endpoints for Content Repository Hotels"
 * ),
 * @OA\Tag(
 *      name="Content Repository | Room API",
 *      description="API Endpoints for Content Repository Rooms"
 *  ),
 * @OA\Tag(
 *     name="Insurance API",
 *     description="API Endpoints for Insurance"
 * )
 */
class BaseController extends Controller
{
    /**
     * success response method.
     */
    public function sendResponse(array $result, ?string $message = null, ?int $code = 200): JsonResponse
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

        return response()->json($response, $code);
    }

    /**
     * return error response.
     */
    public function sendError($error, string $errorMessages = '', int $code = 400, array $data = []): JsonResponse
    {
        $response = [
            'data'    => $data,
            'success' => false,
            'error'   => $error,
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
                $query->where($field, 'like', '%' . request()->input($field) . '%');
            }
        }

        return $query;
    }
}
