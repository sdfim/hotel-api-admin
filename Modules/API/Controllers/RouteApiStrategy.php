<?php

namespace Modules\API\Controllers;

use Illuminate\Http\JsonResponse;

class RouteApiStrategy
{

    /**
     * @param $supplier
     * @param $type
     * @return mixed
     */
    public function getHandler($supplier, $type): mixed
    {
        $supplier = ucfirst($supplier);
        $type = ucfirst($type);
        $nameClass = "Modules\\API\\Controllers\\" . ucfirst($supplier) . ucfirst($type) . 'ApiHandler';
        if (!class_exists($nameClass)) {
            return response()->json(['message' => 'Handler class not found'], 400);
        }
        return  new $nameClass();
    }
}
