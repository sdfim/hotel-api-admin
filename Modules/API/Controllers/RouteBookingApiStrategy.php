<?php

namespace Modules\API\Controllers;

use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;

class RouteBookingApiStrategy
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
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function getHandler($supplier, $type)
    {
        $supplier = ucfirst($supplier);
        $type = ucfirst($type);
        $nameClass = "Modules\\API\\BookingAPI\\" . ucfirst($supplier) . ucfirst($type) . 'BookingApiHandler';
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
