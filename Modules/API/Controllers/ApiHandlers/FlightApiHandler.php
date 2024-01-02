<?php

namespace Modules\API\Controllers\ApiHandlers;

use Modules\API\Controllers\ApiHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;

class FlightApiHandler extends BaseController implements ApiHandlerInterface
{
    /**
     *
     */
    private const SUPPLIER_NAME = 'Expedia';

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
		return response()->json(['message' => 'This page is in development'], 503);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function detail(Request $request): JsonResponse
    {
		return response()->json(['message' => 'This page is in development'], 503);
    }

    /**
     * @param Request $request
     * @param array $suppliers
     * @return JsonResponse
     */
    public function price(Request $request, array $suppliers): JsonResponse
    {
		return response()->json(['message' => 'This page is in development'], 503);
    }
}
