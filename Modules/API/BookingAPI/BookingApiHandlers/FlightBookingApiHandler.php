<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use Modules\API\BookingAPI\BookingApiHandlerInterface;

class FlightBookingApiHandler extends BaseController implements BookingApiHandlerInterface
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

}
