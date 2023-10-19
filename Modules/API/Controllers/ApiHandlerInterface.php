<?php

namespace Modules\API\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface ApiHandlerInterface
{
    public function search (Request $request, array $suppliers): JsonResponse;
    public function detail (Request $request, array $suppliers): JsonResponse;
    public function price (Request $request, array $suppliers): JsonResponse;
}
