<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Modules\API\Controllers\RouteBookingApiStrategy;
use Modules\API\BookingAPI\BookingApiHandlers\HotelBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\FlightBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\ComboBookingApiHandler;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use App\Models\ApiBookingInspector;
use App\Models\ApiSearchInspector;

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
	private ExpediaService $experiaService;
	private RouteBookingApiStrategy $strategy;
	private ApiBookingInspector $bookingInspector;
	private ApiSearchInspector $searchInspector;
	private string|null $type;
	private string|null $supplier;
	private string|null $route;

	public function __construct(RouteBookingApiStrategy $strategy, ExpediaService $experiaService) {
		$this->strategy = $strategy;
		$this->experiaService = $experiaService;
		$this->bookingInspector = new ApiBookingInspector();
		$this->searchInspector = new ApiSearchInspector();
		$this->type = null;
		$this->supplier = null;
		$this->route = null;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function handle(Request $request): mixed
	{
		$this->determinant($request);
		if (!self::isTypeValid($this->type)) return response()->json(['message' => 'Invalid type'], 400);
		if (!self::isRouteValid($this->route)) return response()->json(['message' => 'Invalid route'], 400);
		if (is_null($this->supplier)) return response()->json(['message' => 'Invalid supplier'], 400);

		$dataHandler = match ($this->type) {
			'hotel' => new HotelBookingApiHandler($this->experiaService),
			'flight' => new FlightBookingApiHandler(),
			'combo' => new ComboBookingApiHandler(),
			default => response()->json(['message' => 'Invalid route'], 400),
		};

		return match ($this->route) {
			'addItem' => $dataHandler->addItem($request, $this->supplier),
			'removeItem' => $dataHandler->removeItem($request, $this->supplier),
			'retrieveItems' => $dataHandler->retrieveItems($request, $this->supplier),
			'changeItems' => $dataHandler->changeItems($request, $this->supplier),
			'listBookings' => $dataHandler->listBookings($request, $this->supplier),

			'addPassengers' => $dataHandler->addPassengers($request, $this->supplier),
			'book' => $dataHandler->book($request, $this->supplier),
			'retrieveBooking' => $dataHandler->retrieveBooking($request, $this->supplier),
			'cancelBooking' => $dataHandler->cancelBooking($request, $this->supplier),
			default => response()->json(['message' => 'Invalid route'], 400),
		};
	}

	private function determinant(Request $request) : void
	{
		$this->type = $request->get('type') ?? null;
		$this->supplier = $request->get('supplier') ?? null;
		# Autodetect type by search_id
		if ($request->get('search_id') && $this->type == null) {
			$this->type = $this->searchInspector->geTypeBySearchId($request->get('search_id'));
		}
		# Autodetect type and supplier by booking_id
		if ($request->get('booking_id') && $this->type == null) {
			$bi = $this->bookingInspector->geTypeSupplierByBookingId($request->get('booking_id'));
			$this->type = $bi['type'];
			$this->supplier = $bi['supplier'];
		}

		$this->route = \Route::currentRouteName();

	}

	private static function isTypeValid($value): bool
	{
		return in_array($value, [self::TYPE_HOTEL, self::TYPE_FLIGHT, self::TYPE_COMBO], true);
	}

	private static function isRouteValid($value): bool
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
