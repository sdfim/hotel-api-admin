<?php

namespace Modules\API\Controllers;

use Modules\API\Controllers\HotelApiHandler;
use Modules\API\Controllers\FlightApiHandler;
use Modules\API\Controllers\ComboApiHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoteApiController extends Controller
{
	private const DEFAULT_SUPPLIER = 'expedia';
	private const TYPE_HOTEL = 'hotel';
	private const TYPE_FLIGHT = 'flight';
	private const TYPE_COMBO = 'combo';
	private const ROUTE_SEARCH = 'search';
	private const ROUTE_DETAIL = 'detail';
	private const ROUTE_PRICE = 'price';

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

		// TODO: Get supplier from DB use config Admin Panel
		$supplier = self::DEFAULT_SUPPLIER;
        $handlerClassName = "Modules\\API\\Controllers\\" . ucfirst($supplier) . ucfirst($type) . 'ApiHandler';
		if (!class_exists($handlerClassName)) {
			return response()->json(['message' => 'Handler class not found'], 400);
		}
		$dataHandler = new $handlerClassName();

		// match ($type) {
		// 	'hotel' => $dataHandler = new HotelApiHandler(),
		// 	'flight' => $dataHandler = new FlightApiHandler(),
		// 	'combo' => $dataHandler = new ComboApiHandler(),
		// 	default => response()->json(['message' => 'Invalid type'], 400),
		// };

		return match ($route) {
			'search' => $dataHandler->search($request),
			'detail' => $dataHandler->detail($request),
			'price' => $dataHandler->price($request),
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
