<?php

namespace Modules\API\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;

interface ApiHandlerInterface
{
    public function search(Request $request): JsonResponse;

    public function detail(Request $request): JsonResponse;

    public function price(Request $request, array $suppliers): JsonResponse|StreamedJsonResponse;
}
