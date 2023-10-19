<?php

namespace Modules\API\Controllers;

use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;

class RouteApiStrategy
{
	private ExperiaService $experiaService;

	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
	}

	public function getHandler($supplier, $type) 
	{
		$supplier = ucfirst($supplier);
		$type = ucfirst($type);
		$nameClass = "Modules\\API\\Controllers\\" . ucfirst($supplier) . ucfirst($type) . 'ApiHandler';
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
