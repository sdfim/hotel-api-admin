<?php

namespace Modules\API\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;

class RouteApiStrategy
{
    /**
     * @var ExpediaService
     */
    private ExpediaService $expediaService;

    /**
     * @param ExpediaService $expediaService
     */
    public function __construct(ExpediaService $expediaService)
    {
        $this->expediaService = $expediaService;
    }

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
        if ($nameClass == ExpediaHotelApiHandler::class) {
            $dataHandler = new $nameClass($this->expediaService);
        } else {
            $dataHandler = new $nameClass();
        }
        return $dataHandler;
    }
}
