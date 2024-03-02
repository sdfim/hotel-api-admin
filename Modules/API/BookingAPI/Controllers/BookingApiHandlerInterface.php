<?php

namespace Modules\API\BookingAPI\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface BookingApiHandlerInterface
{
    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addItem(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function removeItem(Request $request, string $supplier): JsonResponse;

}
