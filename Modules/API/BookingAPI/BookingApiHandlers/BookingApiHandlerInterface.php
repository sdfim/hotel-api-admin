<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface BookingApiHandlerInterface
{
    public function addItem(Request $request, string $supplier): JsonResponse;

    public function removeItem(Request $request, string $supplier): JsonResponse;
}
