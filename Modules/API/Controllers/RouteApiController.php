<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Modules\API\Controllers\RouteApiStrategy;
use Modules\API\Controllers\ApiHendlers\HotelApiHanlder;
use Modules\API\Controllers\ApiHendlers\FlightApiHandler;
use Modules\API\Controllers\ApiHendlers\ComboApiHandler;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;

class RouteApiController extends Controller
{


	private const DEFAULT_SUPPLIER = 'expedia';
	private const TYPE_HOTEL = 'hotel';
	private const TYPE_FLIGHT = 'flight';
	private const TYPE_COMBO = 'combo';
	private const ROUTE_SEARCH = 'search';
	private const ROUTE_DETAIL = 'detail';
	private const ROUTE_PRICE = 'price';
	private RouteApiStrategy $strategy;
	private ExperiaService $experiaService;

	public function __construct(RouteApiStrategy $strategy, ExperiaService $experiaService) {
		$this->strategy = $strategy;
		$this->experiaService = $experiaService;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function handle(Request $request): mixed
	{
		$type = $request->get('type');
		$route = \Route::currentRouteName();

		if (!self::isTypeValid($type)) return response()->json(['message' => 'Invalid type'], 400);
		if (!self::isRouteValid($route)) return response()->json(['message' => 'Invalid route'], 400);

		// TODO: [UJV-3] Get supplier from DB use config Admin Panel
		$expedia = self::DEFAULT_SUPPLIER;
		$expediaId = Suppliers::where('name', $expedia)->first()->id;
		$suppliersIds = [$expediaId];

		// $dataHandler = $this->strategy->getHandler($supplier, $type);

		$dataHandler = match ($type) {
			'hotel' => new HotelApiHanlder($this->experiaService),
			'flight' => new FlightApiHandler(),
			'combo' => new ComboApiHandler(),
			default => response()->json(['message' => 'Invalid route'], 400),
		};

		return match ($route) {
			'search' => $dataHandler->search($request, $suppliersIds),
			'detail' => $dataHandler->detail($request,$suppliersIds),
			'price' => $dataHandler->price($request, $suppliersIds),
			default => response()->json(['message' => 'Invalid route'], 400),
		};
	}

	public static function isTypeValid($value): bool
	{
		return in_array($value, [self::TYPE_HOTEL, self::TYPE_FLIGHT, self::TYPE_COMBO], true);
	}

	public static function isRouteValid($value): bool
	{
		return in_array($value, [self::ROUTE_SEARCH, self::ROUTE_DETAIL, self::ROUTE_PRICE], true);
	}
}
