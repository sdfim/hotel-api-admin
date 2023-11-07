<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Modules\API\BaseController;
use Modules\API\BookingApi\BookingApiHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComboBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addItem(Request $request, string $supplier): JsonResponse
    {
        return response()->json(['message' => 'This page is in development'], 503);
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function removeItem(Request $request, string $supplier): JsonResponse
    {
		return response()->json(['message' => 'This page is in development'], 503);
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addPassengers(Request $request, string $supplier): JsonResponse
    {
		return response()->json(['message' => 'This page is in development'], 503);
    }
    
}
