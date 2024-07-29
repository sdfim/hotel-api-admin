<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use Modules\API\BookingAPI\Controllers\BookingApiHandlerInterface;

class ComboBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
    public function addItem(Request $request, string $supplier): JsonResponse
    {
        return response()->json(['message' => 'This page is in development'], 503);
    }

    public function removeItem(Request $request, string $supplier): JsonResponse
    {
        return response()->json(['message' => 'This page is in development'], 503);
    }
}
