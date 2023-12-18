<?php
declare(strict_types=1);

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BaseController;
use Modules\API\BookingAPI\ExpediaBookApiHandler;
use Modules\API\Requests\BookingBookRequest;
use Modules\API\Requests\BookingChangeBookHotelRequest;
use Modules\API\Requests\BookingAddPassengersHotelRequest as AddPassengersRequest;
use Carbon\Carbon;


/**
 * @OA\PathItem(
 * path="/api/booking",
 * )
 */
class BookApiHandler extends BaseController
{
    /**
     * @var ExpediaBookApiHandler
     */
    private ExpediaBookApiHandler $expedia;
    /**
     *
     */
    private const EXPEDIA_SUPPLIER_NAME = 'Expedia';

	private const AGE_ADULT = 16;


    /**
     * @param ExpediaBookApiHandler $expedia
     */
    public function __construct(ExpediaBookApiHandler $expedia)
    {
        $this->expedia = $expedia;
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    /**
     * @OA\Post(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/book",
     *   summary="Create a new booking for a service or event",
     *   description="Create a new booking for a service or event. Use this endpoint to make reservations.",
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="To retrieve the **booking_id**, you need to execute a **'/api/booking/add-item'** request. <br>
     *      In the response object for each rate is a **booking_id** property.",
     *   ),
	 *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingBookRequest",
     *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingBookRequest", example="BookingBookRequest"),
	 *           "example2": @OA\Schema(ref="#/components/examples/BookingBookRequestExpedia", example="BookingBookRequestExpedia"),
     *       },
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingBookResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/BookingBookResponse", example="BookingBookResponse"),
	 *       }
	 *     )
     *   ),
	 *   @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingBookResponseErrorItem",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/BookingBookResponseErrorItem", example="BookingBookResponseErrorItem"),
	 *       "example2": @OA\Schema(ref="#/components/examples/BookingBookResponseErrorBooked", example="BookingBookResponseErrorBooked"),
	 *       }
	 *     )
     *   ),
	 *   @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function book(Request $request): JsonResponse
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

		$validate = Validator::make($request->all(), (new BookingBookRequest())->rules());
        if ($validate->fails()) return $this->sendError($validate->errors());

        $filters = $request->all();

        $items = BookRepository::notBookedItems($request->booking_id);

        if (!$items->count()) {
            return $this->sendError(['error' => 'No items to book OR the order cart (booking_id) is complete/booked'], 'failed');
        }

        if (isset($request->special_requests)) {
            $arrItems = $items->pluck('booking_item')->toArray();
            foreach ($request->special_requests as $item) {
                if (!in_array($item['booking_item'], $arrItems)) {
                    return $this->sendError(['error' => 'special_requests must be in valid booking_item. ' .
                        'Valid booking_items: ' . implode(',', $arrItems)], 'failed');
                }
            }
        }

        $data = [];
        foreach ($items as $item) {
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first();
                if ($supplier->name == self::EXPEDIA_SUPPLIER_NAME) {
                    $data[] = $this->expedia->book($filters, $item);
                }
                // TODO: Add other suppliers
            } catch (Exception $e) {
                Log::error('BookApiHandler | book ' . $e->getMessage());
                $data[] = [
                    'booking_id' => $item->booking_id,
                    'booking_item' => $item->booking_item,
                    'search_id' => $item->search_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        foreach ($data as $item) {
            if (isset($item['error'])) {
                return $this->sendError($item);
            }
        }

        return $this->sendResponse($data, 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    /**
     * @OA\Put(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/change-booking",
     *   summary="Modify an existing booking..",
     *   description="Modify an existing booking. You can update booking details, change dates, or make other adjustments to your reservation.",
     *   @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *      example="3333cee5-b4a3-4e51-bfb0-02d09370b585"
     *    ),
     *   @OA\Parameter(
     *      name="booking_item",
     *      in="query",
     *      required=true,
     *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
     *      In the response object for each rate is a **booking_item** property.",
     *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *    ),
     *     @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingChangeBookingRequest",
     *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingChangeBookingRequest", example="BookingChangeBookingRequest"),
     *       },
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingChangeBookingResponse",
     *           examples={
     *             "example1": @OA\Schema(ref="#/components/examples/BookingChangeBookingResponse", example="BookingChangeBookingResponse"),
     *         },
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BookingChangeBookingResponseError", example="BookingChangeBookingResponseError"),
     *       },
     *     )
     *   ),
	 *   @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function changeBooking(Request $request): JsonResponse
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

		$validate = Validator::make($request->all(), (new BookingChangeBookHotelRequest())->rules());
        if ($validate->fails()) return $this->sendError($validate->errors());

		if (!BookRepository::isBook($request->booking_id, $request->booking_item)) {
			return $this->sendError(['error' => 'booking_id and/or booking_item not yet booked'], 'failed');
		}

		$filters = $request->all();

        $supplierId = ApiBookingItem::where('booking_item', $request->booking_item)->first()->supplier_id;
        $supplier = Supplier::where('id', $supplierId)->first()->name;

        try {
            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->changeBooking($filters);
            }
            // TODO: Add other suppliers

        } catch (Exception $e) {
            Log::error('BookApiHandler | changeItems ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        if (isset($data['errors'])) {
            return $this->sendError($data['errors'], $data['message']);
        }

        return $this->sendResponse($data ?? [], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/list-bookings",
     *   summary="Retrieve a list of all your booking reservations. ",
     *   description="Retrieve a list of all your booking reservations. This endpoint provides an overview of your booking history and their current statuses.",
     *    @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      description="Type",
     *      @OA\Schema(
     *        type="string",
     *        example="hotel"
     *      )
     *    ),
     *    @OA\Parameter(
     *      name="supplier",
     *      in="query",
     *      required=true,
     *      description="Supplier",
     *      @OA\Schema(
     *        type="string",
     *        example="Expedia"
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *    ),
     *    @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
     *   @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BadRequestResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
	 *       }
	 *     )
	 *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function listBookings(Request $request): JsonResponse
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

		$validate = Validator::make($request->all(), [
			'supplier' => 'required|string',
			'type' => 'required|string|in:hotel,flight,combo'
		]);
		if ($validate->fails()) return $this->sendError($validate->errors());

        $supplier = $request->supplier;
        try {
            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->listBookings();
            }
            // TODO: Add other suppliers
        } catch (Exception $e) {
            Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/retrieve-booking",
     *   summary="Retrieve detailed information about a specific booking reservation. ",
     *   description="Retrieve detailed information about a specific booking reservation. This endpoint allows you to access all the information related to a particular reservation.",
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *      @OA\Schema(
     *        type="string",
     *        example="5a67bbbc-0c30-47d9-8b01-ef70c2da196f"
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *     @OA\JsonContent(
     *     ref="#/components/schemas/BookingRetrieveBookingResponse",
     *     examples={
     *     "example1": @OA\Schema(ref="#/components/examples/BookingRetrieveBookingResponse", example="BookingRetrieveBookingResponse"),
     *     }
     *     )
     *    ),
     *    @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
     *   @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BadRequestResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
	 *       }
	 *     )
	 *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function retrieveBooking(Request $request): JsonResponse
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

		$filters = $request->all();
		$validate = Validator::make($request->all(), ['booking_id' => 'required|size:36']);
        if ($validate->fails()) return $this->sendError($validate->errors());

        $itemsBooked = BookRepository::bookedItems($request->booking_id);
        $data = [];
        foreach ($itemsBooked as $item) {
			if (!BookRepository::isBook($request->booking_id, $item->booking_item)) {
				$data[] = ['error' => 'booking_id and/or booking_item not yet booked'];
				continue;
			}
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;

                if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                    $data[] = $this->expedia->retrieveBooking($filters, $item);
                }
                // TODO: Add other suppliers

            } catch (Exception $e) {
                Log::error('BookApiHandler | retrieveBooking ' . $e->getMessage());
                $data[] = [
                    'booking_id' => $item['booking_id'],
                    'booking_item' => $item['booking_item'],
                    'search_id' => $item['search_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }
		if (empty($data)) {
			return $this->sendError(['error' => 'booking_id not yet booked'], 'failed');
		}

        return $this->sendResponse(['result' => $data], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    /**
     * @OA\Delete(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/cancel-booking",
     *   summary="Cancel an existing booking reservation. Submit a request to cancel a reservation you no longer require. ",
     *   description="Cancel Booking",
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *      example="3333cee5-b4a3-4e51-bfb0-02d09370b585"
     *    ),
     *    @OA\Parameter(
     *      name="booking_item",
     *      in="query",
     *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
     *      In the response object for each rate is a **booking_item** property. <br>
     *      If there is no booking_item, all items will be deleted",
     *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *      @OA\JsonContent(
     *        ref="#/components/schemas/BookingCancelBookingResponse",
     *        examples={
     *        "example1": @OA\Schema(ref="#/components/examples/BookingCancelBookingResponse", example="BookingCancelBookingResponse"),
     *        }
     *      )
     *    ),
	 *    @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BadRequestResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
	 *       }
	 *      )
	 *    ),
	 *    @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function cancelBooking(Request $request): JsonResponse
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

		$validate = Validator::make($request->all(), [
			'booking_id' => 'required|size:36',
			'booking_item' => 'nullable|size:36'
		]);
        if ($validate->fails()) return $this->sendError($validate->errors());

		if (isset($request->booking_item)) {
			$itemsBooked = BookRepository::bookedItem($request->booking_id, $request->booking_item);
		} else {
			$itemsBooked = BookRepository::bookedItems($request->booking_id);
		}

		// TODO: add validation for request
        $filters = $request->all();
        $data = [];
        foreach ($itemsBooked as $item) {
			if (!BookRepository::isBook($request->booking_id, $item->booking_item)) {
				$data[] = ['error' => 'booking_id and/or booking_item not yet booked'];
				continue;
			}
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;

                if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                    $data[] = $this->expedia->cancelBooking($filters, $item);
                }
                // TODO: Add other suppliers

            } catch (Exception $e) {
                Log::error('BookApiHandler | cancelBooking ' . $e->getMessage());
                $data[] = [
                    'booking_id' => $item['booking_id'],
                    'booking_item' => $item['booking_item'],
                    'search_id' => $item['search_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }
		if (empty($data)) {
			return $this->sendError(['error' => 'booking_id not yet booked'], 'failed');
		}

        return $this->sendResponse(['result' => $data], 'success');
	}

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *   tags={"Booking API | Cart Endpoints"},
     *   path="/api/booking/retrieve-items",
     *   summary="Retrieve a list of items currently in your shopping cart.",
     *   description="Retrieve a list of items currently in your shopping cart. This endpoint provides details about the items added to your cart.",
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *      @OA\Schema(
     *        type="string",
     *        example="5a67bbbc-0c30-47d9-8b01-ef70c2da196f"
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *      @OA\JsonContent(
     *        ref="#/components/schemas/BookingRetrieveItemsResponse",
     *        examples={
     *        "example1": @OA\Schema(ref="#/components/examples/BookingRetrieveItemsResponse", example="BookingRetrieveItemsResponse"),
     *        }
     *      )
     *    ),
     *    @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
     *   @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BadRequestResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
	 *       }
	 *     )
	 *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function retrieveItems(Request $request): JsonResponse
    {
		$determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

        $filters = $request->all();
		$validate = Validator::make($request->all(), ['booking_id' => 'required|size:36']);
        if ($validate->fails()) return $this->sendError($validate->errors());

        $itemsInCart = BookRepository::getItemsInCart($request->booking_id);

        $res = [];
        try {
            foreach ($itemsInCart as $item) {

                if (BookRepository::isBook($request->booking_id, $item->booking_item)) {
                    return $this->sendError(['error' => 'Cart is empty or booked'], 'failed');
                }

                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;

                if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                    $res[] = $this->expedia->retrieveItem($filters, $item);
                }
                // TODO: Add other suppliers
            }

        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | retrieveItems ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        return $this->sendResponse(['result' => $res], 'success');

    }

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	/**
	 * @OA\Post(
	 *   tags={"Booking API | Cart Endpoints"},
	 *   path="/api/booking/add-passengers",
	 *   summary="Add passengers to a booking.",
	 *   description="Add passengers to a booking. This endpoint is used to add passenger information to a booking.",
	 *     @OA\Parameter(
	 *       name="booking_id",
	 *       in="query",
	 *       required=true,
	 *       description="To retrieve the **booking_id**, you need to execute a **'/api/booking/add-item'** request. <br>
	 *       In the response object for each rate is a **booking_id** property.",
	 *     ),
	 *     @OA\RequestBody(
	 *     description="JSON object containing the details of the reservation. If you don't pass booking_item(s), these passengers will be added to all booking_items that are in the cart (booking_id)",
	 *     required=true,
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingAddPassengersRequest",
	 *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingAddPassengersRequest", example="BookingAddPassengersRequest"),
     *       },
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingAddPassengersResponse",
	 *       examples={
	 *           "Add": @OA\Schema(ref="#/components/examples/BookingAddPassengersResponseAdd", example="BookingAddPassengersResponseAdd"),
	 *           "Update": @OA\Schema(ref="#/components/examples/BookingAddPassengersResponseUpdate", example="BookingAddPassengersResponseUpdate"),
	 *       },
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingAddPassengersResponse",
	 *       examples={
	 *       "Error": @OA\Schema(ref="#/components/examples/BookingAddPassengersResponseError", example="BookingAddPassengersResponseError"),
	 *       },
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=401,
	 *     description="Unauthenticated",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/UnAuthenticatedResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
	 *       }
	 *     )
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function addPassengers(Request $request): JsonResponse
	{
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);

        $filters = $request->all();
        $validate = Validator::make($request->all(), ['booking_id' => 'required|size:36']);
        if ($validate->fails()) return $this->sendError($validate->errors());

		$filters = Validator::make($request->all(), (new AddPassengersRequest())->rules());
        if ($filters->fails()) return $this->sendError($filters->errors());

		$filters = $request->all();
		$filtersOutput = $this->dtoAddPassengers($filters);
		$checkData = $this->checkCountGuestsChildrenAges($filtersOutput);
		if (!empty($checkData)) return $this->sendError($checkData, 'failed');

        $itemsInCart = BookRepository::getItemsInCart($request->booking_id);

		$bookingRequestItems = array_keys($filtersOutput);
		foreach ($bookingRequestItems as $requestItem) {
			if (!in_array($requestItem, $itemsInCart->pluck('booking_item')->toArray()))
				return $this->sendError(['error' => 'This booking_item is not in the cart.'], 'failed');
		}

		try {
            $result = [];
			foreach ($bookingRequestItems as $booking_item) {

                if (BookRepository::isBook($request->booking_id, $booking_item)) {
                    return $this->sendError(['error' => 'Cart is empty or booked'], 'failed');
                }
				$supplierId = ApiBookingItem::where('booking_item', $booking_item)->first()->supplier_id;
                $supplier = Supplier::where('id', $supplierId)->first()->name;

                if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
					$filters = $request->all();
					$filters['booking_item'] = $booking_item;
                    $res[] = $this->expedia->addPassengers($filters, $filtersOutput[$booking_item]);
                }
                // TODO: Add other suppliers
            }
		} catch (Exception $e) {
			Log::error('HotelBookingApiHandler | listBookings ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		return $this->sendResponse(['result' => $res], 'success');
	}

	/**
     * @param Request $request
     * @return array
     */
    private function determinant(Request $request): array
    {
		$requestTokenId = PersonalAccessToken::findToken($request->bearerToken())->id;
		$dbTokenId = null;

		# check Owner token
		if($request->has('booking_item')) {
			if (!$this->validatedUuid('booking_item')) return [];
			$apiBookingItem = ApiBookingItem::where('booking_item', $request->booking_item)->with('search')->first();
			if (!$apiBookingItem) return ['error' => 'Invalid booking_item'];
			$dbTokenId = $apiBookingItem->search->token_id;
			if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
		}

		# check Owner token
      if ($request->has('booking_id')) {

			if (!$this->validatedUuid('booking_id')) return ['error' => 'Invalid booking_id'];
            $bi = BookRepository::geTypeSupplierByBookingId($request->booking_id);
			if (empty($bi)) return ['error' => 'Invalid booking_id'];
			$dbTokenId = $bi['token_id'];

			if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
        }

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
			return false;
		}
		return true;
	}

    /**
     * @param array $input
     * @return array
     */
    private function dtoAddPassengers (array $input) : array
	{
        $output = [];
		foreach ($input['passengers'] as $passenger) {
			foreach ($passenger['booking_items'] as $booking) {
				$bookingItem = $booking['booking_item'];

				# type hotel
				if (isset($booking['room'])) {
					$room = $booking['room'];
					if (isset($output[$bookingItem])) {
						$output[$bookingItem]['rooms'][$room]['passengers'][] = [
							'title' => $passenger['title'],
							'given_name' => $passenger['given_name'],
							'family_name' => $passenger['family_name'],
							'date_of_birth' => $passenger['date_of_birth']
						];
					} else {
						$output[$bookingItem] = [
							'booking_item' => $bookingItem,
							'rooms' => [
								$room => [
									'passengers' => [
										[
											'title' => $passenger['title'],
											'given_name' => $passenger['given_name'],
											'family_name' => $passenger['family_name'],
											'date_of_birth' => $passenger['date_of_birth']
										]
									]
								]
							]
						];
					}
				}
				# type flight
                if (!isset($booking['room'])) {
					if (isset($output[$bookingItem])) {
						$output[$bookingItem]['passengers'][] = [
							'title' => $passenger['title'],
							'given_name' => $passenger['given_name'],
							'family_name' => $passenger['family_name'],
							'date_of_birth' => $passenger['date_of_birth']
						];
					} else {
						$output[$bookingItem] = [
							'booking_item' => $bookingItem,
							'passengers' => [
								[
									'title' => $passenger['title'],
									'given_name' => $passenger['given_name'],
									'family_name' => $passenger['family_name'],
									'date_of_birth' => $passenger['date_of_birth']
								]
							]
						];
					}
				}

			}
		}

		return $output;
	}

    /**
     * @param array $filtersOutput
     * @return array|string[]
     */
    private function checkCountGuestsChildrenAges (array $filtersOutput) : array
	{
		foreach ($filtersOutput as $bookingItem => $booking) {
			$search = ApiBookingItem::where('booking_item', $bookingItem)->first();

			if (!$search) return ['booking_item' => 'Invalid booking_item'];

			$type = ApiSearchInspector::where('search_id', $search->search_id)->first()->search_type;

			if ($type == 'flight') continue;
			if ($type == 'combo') continue;
			if ($type == 'hotel') return $this->checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $search->search_id);
		}

		return [];
	}

    /**
     * @param $bookingItem
     * @param $booking
     * @param $searchId
     * @return array
     */
    private function checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $searchId) : array
	{
		$searchData = json_decode(ApiSearchInspector::where('search_id', $searchId)->first()->request, true);

			foreach ($booking['rooms'] as $room => $roomData) {

				$ages = [];
				foreach ($roomData['passengers'] as $passenger) {
                    $dob = Carbon::parse($passenger['date_of_birth']);
                    $now = Carbon::now();
                    $ages[] = $now->diffInYears($dob);
				}

				$childrenCount = 0;
				$adultsCount = 0;
				foreach ($ages as $age) {
					if ($age < self::AGE_ADULT) $childrenCount++;
					else $adultsCount++;
				}

				if ($adultsCount != $searchData['occupancy'][$room - 1]['adults'])
					return [
						'type' => 'The number of adults not match.',
						'booking_item' => $bookingItem,
						'search_id' => $searchId,
						'room' => $room,
						'number_of_adults_in_search' => $searchData['occupancy'][$room - 1]['adults'],
						'number_of_adults_in_query' => $adultsCount
						];
				if (!isset($searchData['occupancy'][$room - 1]['children_ages']) && $childrenCount != 0)
					return [
						'type' => 'The number of children not match.',
						'booking_item' => $bookingItem,
						'search_id' => $searchId,
						'room' => $room,
						'number_of_children_in_search' => 0,
						'number_of_children_in_query' => $childrenCount
						];

				if (!isset($searchData['occupancy'][$room - 1]['children_ages'])) continue;

				if ($childrenCount != count($searchData['occupancy'][$room - 1]['children_ages']))
					return [
						'type' => 'The number of children not match.',
						'booking_item' => $bookingItem,
						'search_id' => $searchId,
						'room' => $room,
						'number_of_children_in_search' => count($searchData['occupancy'][$room - 1]['children_ages']),
						'number_of_children_in_query' => $childrenCount
						];

				$childrenAges = $searchData['occupancy'][$room - 1]['children_ages'];
				sort($childrenAges);
				$childrenAgesInQuery = [];
				foreach ($roomData['passengers'] as $passenger) {
					$givenDate = Carbon::create($passenger['date_of_birth']);
					$currentDate = Carbon::now();
					$years = $givenDate->diffInYears($currentDate);
					if ($years >= self::AGE_ADULT) continue;
					$childrenAgesInQuery[] = $years;
				}
				sort($childrenAgesInQuery);
				if ($childrenAges != $childrenAgesInQuery) {
					return [
						'type' => 'Children ages not match.',
						'booking_item' => $bookingItem,
						'search_id' => $searchId,
						'room' => $room,
						'children_ages_in_search' => implode(',', $childrenAges) ,
						'children_ages_in_query' => implode(',', $childrenAgesInQuery)
						];
				}
			}
		return [];
	}
}
