<?php

namespace Modules\API\Controllers;

use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;

class RouteBookingApiStrategy
{
	private ExpediaService $experiaService;

	public function __construct(ExpediaService $experiaService) {
		$this->experiaService = $experiaService;
	}

	public function getHandler($supplier, $type)
	{
		$supplier = ucfirst($supplier);
		$type = ucfirst($type);
		$nameClass = "Modules\\API\\BookingAPI\\" . ucfirst($supplier) . ucfirst($type) . 'BookingApiHandler';
		if (!class_exists($nameClass)) {
			return response()->json(['message' => 'Handler class not found'], 400);
		}
		if ($nameClass == ExpediaHotelApiHandler::class) {
			$dataHandler = new $nameClass($this->experiaService);
		} else {
			$dataHandler = new $nameClass();
		}
		return $dataHandler;
	}


}
