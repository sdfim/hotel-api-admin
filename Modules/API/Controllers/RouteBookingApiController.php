<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApiBookingItem;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BookingAPI\BookingApiHandlers\HotelBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\FlightBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\ComboBookingApiHandler;
use App\Models\ApiBookingInspector;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;


class RouteBookingApiController extends Controller
{
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

	private const ROUTE_CHANGE_BOOKING = 'changeBooking';

    /**
     * @var string|null
     */
    private string|null $type;

    /**
     * @var string|null
     */
    private string|null $supplier;

    /**
     * @var string|null
     */
    private string|null $route;

    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request): mixed
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);
        if (!self::isTypeValid($this->type)) return response()->json(['message' => 'Invalid type'], 400);
        if (!self::isRouteValid($this->route)) return response()->json(['message' => 'Invalid route'], 400);
        if (is_null($this->supplier)) return response()->json(['message' => 'Invalid supplier'], 400);

        $dataHandler = match ($this->type) {
            'hotel' => new HotelBookingApiHandler(),
            'flight' => new FlightBookingApiHandler(),
            'combo' => new ComboBookingApiHandler(),
            default => response()->json(['message' => 'Invalid route'], 400),
        };

        return match ($this->route) {
            'addItem' => $dataHandler->addItem($request, $this->supplier),
            'removeItem' => $dataHandler->removeItem($request, $this->supplier),
            default => response()->json(['message' => 'Invalid route'], 400),
        };
    }

    /**
     * @param Request $request
     * @return array
     */
    private function determinant(Request $request): array
    {
		$this->type = $request->get('type') ?? null;
        $this->supplier = $request->get('supplier') ?? null;

		$requestTokenId = PersonalAccessToken::findToken($request->bearerToken())->id;

		# Autodetect type by booking_item and check Owner token
		if($request->has('booking_item')) {
			if (!$this->validatedUuid('booking_item')) return [];
			$apiBookingItem = ApiBookingItem::where('booking_item', $request->get('booking_item'))->with('search')->first();
			if (!$apiBookingItem) return ['error' => 'Invalid booking_item'];
			$dbTokenId = $apiBookingItem->search->token_id;
			if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
			$this->supplier = Supplier::where('id', $apiBookingItem->supplier_id)->first()->name;
			$this->type = SearchRepository::geTypeBySearchId($apiBookingItem->search_id);
		}

		# Autodetect type and supplier by booking_id and check Owner token
        if ($request->has('booking_id')) {
			if (!$this->validatedUuid('booking_id')) return ['error' => 'Invalid booking_id'];
            $bi = BookingRepository::geTypeSupplierByBookingId($request->get('booking_id'));
			if (empty($bi)) return ['error' => 'Invalid booking_id'];
			$dbTokenId = $bi['token_id'];
			if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
            if ($this->type == null) $this->type = $bi['type'];
            if ($this->supplier == null) $this->supplier = $bi['supplier'];
        }

		# Autodetect type by search_id
        if ($request->has('search_id') && $this->type == null) {
        	if (!$this->validatedUuid('search_id')) return ['error' => 'Invalid search_id'];
            $this->type = SearchRepository::geTypeBySearchId($request->get('search_id'));
        }

        $this->route = Route::currentRouteName();
		return [];
    }

    /**
     * @param $id
     * @return bool
     */
    private function validatedUuid($id) : bool
	{
		$validate = Validator::make(request()->all(), [$id => 'required|size:36']);
        if ($validate->fails()) {
			$this->type = null;
			$this->supplier = null;
			return false;
		};
		return true;
	}

    /**
     * @param $value
     * @return bool
     */
    private static function isTypeValid($value): bool
    {
        return in_array($value, [self::TYPE_HOTEL, self::TYPE_FLIGHT, self::TYPE_COMBO], true);
    }

    /**
     * @param $value
     * @return bool
     */
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
            self::ROUTE_CANCEL_BOOKING,
			self::ROUTE_CHANGE_BOOKING,
        ], true);
    }
}
