<?php

namespace Modules\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return JsonResponse
     */
    public function sendResponse (array $result, ?string $message = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
        ];

		if (empty($response['data'])) unset($response['data']);

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return JsonResponse
     */
    public function sendError ($error, $errorMessages = [], $code = 400): JsonResponse
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
