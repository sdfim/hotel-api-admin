<?php

namespace Modules\API\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface ApiHandlerInterface
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function detail(Request $request): JsonResponse;

    /**
     * @param Request $request
     * @param array $suppliers
     * @return JsonResponse
     */
    public function price(Request $request, array $suppliers): JsonResponse;
}
