<?php

namespace Modules\API\Controllers\ApiHandlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use Modules\API\Controllers\ApiHandlerInterface;

class ComboApiHandler extends BaseController implements ApiHandlerInterface
{
    public function search(Request $request): JsonResponse
    {
        return response()->json(['message' => 'This page is in development'], 503);
    }

    public function detail(Request $request): JsonResponse
    {
        return response()->json(['message' => 'This page is in development'], 503);
    }

    public function price(Request $request, array $suppliers): JsonResponse
    {
        return response()->json(['message' => 'This page is in development'], 503);
    }
}
