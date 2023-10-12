<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Modules\API\Controllers\RouteBookingApiStrategy;
use Modules\API\BookingAPI\BookingApiHendlers\HotelBookingApiHanlder;
use Modules\API\BookingAPI\BookingApiHendlers\FlightBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHendlers\ComboBookingApiHandler;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;

class RouteBookingApiController extends Controller
{


	private const DEFAULT_SUPPLIER = 'expedia';
	private const TYPE_HOTEL = 'hotel';
	private const TYPE_FLIGHT = 'flight';
	private const TYPE_COMBO = 'combo';
	private const ROUTE_ADD_ITEM = 'addItem';
	private const ROUTE_REMOVE_ITEM = 'removeItem';
	private const ROUTE_CHANGE_ITEMS = 'changeItems';
	private const ROUTE_RETRIEVE_ITEMS = 'retrieveItems';
	private const ROUTE_ADD_PASSENGERS = 'addPassengers';
	private const ROUTE_BOOK = 'book';
	private const ROUTE_LIST_BOOKINGS = 'listBookings';
	private const ROUTE_RETRIEVE_BOOKING = 'retrieveBooking';
	private const ROUTE_CANCEL_BOOKING = 'cancelBooking';

	private ExperiaService $experiaService;

	private RouteBookingApiStrategy $strategy;

	public function __construct(RouteBookingApiStrategy $strategy, ExperiaService $experiaService) {
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
		$supplier = $request->get('supplier');
		$route = \Route::currentRouteName();

		if (!self::isTypeValid($type)) return response()->json(['message' => 'Invalid type'], 400);
		if (!self::isRouteValid($route)) return response()->json(['message' => 'Invalid route'], 400);

		// $dataHandler = $this->strategy->getHandler($supplier, $type);
		
		$dataHandler = match ($type) {
			'hotel' => new HotelBookingApiHanlder($this->experiaService),
			'flight' => new FlightBookingApiHandler(),
			'combo' => new ComboBookingApiHandler(),
			default => response()->json(['message' => 'Invalid route'], 400),
		};

		return match ($route) {
			'addItem' => $dataHandler->addItem($request, $supplier),
			'removeItem' => $dataHandler->removeItem($request, $supplier),
			'retrieveItems' => $dataHandler->retrieveItems($request, $supplier),
			'changeItems' => $dataHandler->changeItems($request, $supplier),
			'listBookings' => $dataHandler->listBookings($request, $supplier),

			'addPassengers' => $dataHandler->addPassengers($request, $supplier),
			'book' => $dataHandler->book($request, $supplier),
			'retrieveBooking' => $dataHandler->retrieveBooking($request, $supplier),
			'cancelBooking' => $dataHandler->cancelBooking($request, $supplier),
			default => response()->json(['message' => 'Invalid route'], 400),
		};
	}

	public static function isTypeValid($value): bool
	{
		return in_array($value, [self::TYPE_HOTEL, self::TYPE_FLIGHT, self::TYPE_COMBO], true);
	}

	public static function isRouteValid($value): bool
	{
		return in_array($value, [
			self::ROUTE_ADD_ITEM,
			self::ROUTE_REMOVE_ITEM,
			self::ROUTE_RETRIEVE_ITEMS,
			self::ROUTE_CHANGE_ITEMS,
			self::ROUTE_ADD_PASSENGERS,
			self::ROUTE_BOOK,
			self::ROUTE_LIST_BOOKINGS,
			self::ROUTE_RETRIEVE_BOOKING,
			self::ROUTE_CANCEL_BOOKING
		], true);
	}
}
