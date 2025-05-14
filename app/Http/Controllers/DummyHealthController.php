<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DummyHealthController extends Controller
{
    public function check(): JsonResponse
    {
        return response()->json(['status' => 'alive'], 200);
    }
}
