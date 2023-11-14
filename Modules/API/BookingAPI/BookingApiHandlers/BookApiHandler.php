<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BaseController;
use Modules\API\BookingAPI\ExpediaBookApiHandler;
use Modules\API\Requests\BookingBookRequest;
use Modules\API\Requests\BookingChangeBookHotelRequest;

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

        $itemsBooked = ApiBookingInspector::where('booking_id', $request->booking_id)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->get()
            ->pluck('booking_id')
            ->toArray();

        $items = ApiBookingInspector::where('booking_id', $request->booking_id)
            ->where('type', 'add_item')
            ->where('sub_type', 'like', 'price_check' . '%')
            ->whereNotIn('booking_id', $itemsBooked)
            ->get();

        if (!$items->count()) {
            return $this->sendError(['error' => 'No items to book OR the order cart (booking_id) is complete/booked'], 'failed');
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
                \Log::error('BookApiHandler | book ' . $e->getMessage());
                $data[] = [
                    'booking_id' => $item->booking_id,
                    'booking_item' => $item->booking_item,
                    'search_id' => $item->search_id,
                    'error' => $e->getMessage(),
                ];
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

		if (!ApiBookingInspector::isBook($request->booking_id, $request->booking_item)) {
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
            \Log::error('BookApiHandler | changeItems ' . $e->getMessage());
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
            \Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
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

        $itemsBooked = ApiBookingInspector::bookedItems($request->booking_id);
        $data = [];
        foreach ($itemsBooked as $item) {
			if (!ApiBookingInspector::isBook($request->booking_id, $item->booking_item)) {
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
                \Log::error('BookApiHandler | retrieveBooking ' . $e->getMessage());
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
			$itemsBooked = ApiBookingInspector::bookedItem($request->booking_id, $request->booking_item);
		} else {
			$itemsBooked = ApiBookingInspector::bookedItems($request->booking_id);
		}
		
		// TODO: add validation for request
        $filters = $request->all();
        $data = [];
        foreach ($itemsBooked as $item) {
			if (!ApiBookingInspector::isBook($request->booking_id, $item->booking_item)) {
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
                \Log::error('BookApiHandler | cancelBooking ' . $e->getMessage());
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

        $itemsInCart = ApiBookingInspector::where('booking_id', $request->booking_id)
            ->where('type', 'add_item')
            ->where('sub_type', 'like', 'price_check' . '%')
            ->get();

        $res = [];
        try {
            foreach ($itemsInCart as $item) {

                if (ApiBookingInspector::isBook($request->booking_id, $item->booking_item)) {
                    return $this->sendError(['error' => 'Cart is empty or booked'], 'failed');
                }

                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;

                $data = [];
                if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                    $res[] = $this->expedia->retrieveItem($filters, $item);
                }
                // TODO: Add other suppliers
            }

        } catch (Exception $e) {
            \Log::error('HotelBookingApiHandler | retrieveItems ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        return $this->sendResponse(['result' => $res], 'success');

    }

	/**
     * @param Request $request
     * @return void
     */
    private function determinant(Request $request): array
    {
		$requestTokenId = PersonalAccessToken::findToken($request->bearerToken())->id;
		$dbTokenId = null;

		# chek Owner token 
		if($request->has('booking_item')) {
			if (!$this->validatedUuid('booking_item')) return [];
			$apiBookingItem = ApiBookingItem::where('booking_item', $request->get('booking_item'))->with('search')->first();
			if (!$apiBookingItem) return ['error' => 'Invalid booking_item'];
			$dbTokenId = $apiBookingItem->search->token_id;
			if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
		}

		# chek Owner token 
        if ($request->has('booking_id')) {
			
			if (!$this->validatedUuid('booking_id')) return ['error' => 'Invalid booking_id'];
            $bi = (new ApiBookingInspector())->geTypeSupplierByBookingId($request->get('booking_id'));
			if (empty($bi)) return ['error' => 'Invalid booking_id'];
			$dbTokenId = $bi['token_id'];

			// dd($request->get('booking_id'), $requestTokenId, $dbTokenId, ($dbTokenId !== $requestTokenId));
			if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
        }

		return [];
    }

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
}
