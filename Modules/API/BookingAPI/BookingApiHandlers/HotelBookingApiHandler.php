<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Exception;
use Modules\API\BaseController;
use Modules\API\BookingAPI\BookingApiHandlerInterface;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\Requests\BookingBookHotelRequest;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Illuminate\Support\Facades\Validator;
use Modules\Inspector\SearchInspectorController;
use Modules\API\BookingAPI\ExpediaHotelBookingApiHandler;
use Spatie\FlareClient\Api;

/**
 * @OA\PathItem(
 * path="/api/booking",
 * )
 */
class HotelBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
	/**
	 * @var ExpediaService
	 */
	private ExpediaService $expediaService;
	/**
	 * @var SearchInspectorController
	 */
	private SearchInspectorController $apiInspector;
	/**
	 * @var ExpediaHotelBookingApiHandler
	 */
	private ExpediaHotelBookingApiHandler $expedia;
	/**
	 *
	 */
	private const EXPEDIA_SUPPLIER_NAME = 'Expedia';

	/**
	 * @param ExpediaService $expediaService
	 */
	public function __construct(ExpediaService $expediaService)
	{
		$this->expediaService = $expediaService;
		$this->apiInspector = new SearchInspectorController();
		$this->expedia = new ExpediaHotelBookingApiHandler($this->expediaService);
	}

	/**
	 * @param Request $request
	 * @param string $supplier
	 * @return JsonResponse
	 */
	/**
	 * @OA\Post(
	 *   tags={"Booking API | Cart Endpoints"},
	 *   path="/api/booking/add-item",
	 *   summary="Add an item to your shopping cart.",
	 *   description="Add an item to your shopping cart. This endpoint is used for adding products or services to your cart.",
	 *    @OA\Parameter(
	 *      name="booking_item",
	 *      in="query",
	 *      required=true,
	 *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
	 *      In the response object for each rate is a **booking_item** property.",
	 *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
	 *    ),
	 *    @OA\Parameter(
	 *      name="booking_id",
	 *      in="query",
	 *      description="**booking_id**, if it exists",
	 *      example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
	 *    ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingAddItemResponse", 
	 *		   examples={
	 *             "example1": @OA\Schema(ref="#/components/examples/BookingAddItemResponse", example="BookingAddItemResponse"),
     *         },
	 *     )
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function addItem(Request $request, string $supplier): JsonResponse
	{
		$data = [];

		try {
			$bookingAddItemRequest = new BookingAddItemHotelRequest();
			$rules = $bookingAddItemRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();
			
			if (request()->has('booking_id')) {
				$apiBookingItem = ApiBookingInspector::where('booking_item', request()->get('booking_item'))
					->where('booking_id', request()->get('booking_id'))	
					->first();
				if ($apiBookingItem) {
					return $this->sendError([
						'error' => 'booking_item, booking_id pair is not unique.', 
						'message' => 'This item is already in your cart.'
					]);
				}
				$filters['booking_id'] = request()->get('booking_id');
			}
			// dd($filters);
			if(request()->has('booking_item')) {
				$apiBookingItem = ApiBookingItem::where('booking_item', request()->get('booking_item'))->first()->toArray();
				$filters['search_id'] = $apiBookingItem['search_id'];
			}

			$filters = array_merge($filters, $request->all());		

			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {

				$booking_item_data = json_decode($apiBookingItem['booking_item_data'], true);
				$filters['hotel_id'] = $booking_item_data['hotel_id'];
				$filters['room_id'] = $booking_item_data['room_id'];
				$filters['rate'] = $booking_item_data['rate'];
				$filters['bed_groups'] = $booking_item_data['bed_groups'];

				$data = $this->expedia->addItem($filters);
			}
			// TODO: Add other suppliers
		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | addItem ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);

		return $this->sendResponse($data, 'success');
	}

	/**
	 * @param Request $request
	 * @param string $supplier
	 * @return JsonResponse
	 */
	/**
	 * @OA\Delete(
	 *   tags={"Booking API | Cart Endpoints"},
	 *   path="/api/booking/remove-item",
	 *   summary="Remove a specific item from your shopping cart",
	 *   description="Description: Remove a specific item from your shopping cart. It allows you to modify the contents of your cart.",
	 *    @OA\Parameter(
	 *      name="booking_id",
	 *      in="query",
	 *      required=true,
	 *      description="**booking_id**",
	 *      example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
	 *    ),
	 *    @OA\Parameter(
	 *      name="booking_item",
	 *      in="query",
	 *      required=true,
	 *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
	 *      In the response object for each rate is a **booking_item** property.",
	 *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
	 *    ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingRemoveItemResponse", 
	 *		   examples={
	 *             "example1": @OA\Schema(ref="#/components/examples/BookingRemoveItemResponse", example="BookingRemoveItemResponse"),
     *         },
	 *     )
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function removeItem(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->removeItem($filters);
			}
			// TODO: Add other suppliers

		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | removeItem ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		if (isset($data['error'])) return $this->sendError($data['error']);

		return $this->sendResponse(['result' => $data['success']], 'success');
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
	 *        response=401,
	 *        description="Unauthenticated",
	 *    ),
	 *    @OA\Response(
	 *        response=403,
	 *        description="Forbidden"
	 *    ),
	 *    security={{ "apiAuth": {} }}
	 * )
	 */
	public function retrieveItems(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->retrieveItems($filters);
			}
			// TODO: Add other suppliers

		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | retrieveItems ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}
		if (isset($data['error']))
			return $this->sendError($data['error']);
		else 
			return $this->sendResponse(['result' => $data['success']], 'success');
	}

	/**
	 * @param Request $request
	 * @param string $supplier
	 * @return JsonResponse
	 */
	public function addPassengers(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->addPassengers($filters);
			}
			// TODO: Add other suppliers

		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | listBookings ' . $e->getMessage());
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
	 *    ),     	  
	 *     @OA\RequestBody(
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
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function book(Request $request, string $supplier): JsonResponse
	{
		$data = [];

		try {
			$bookingBookRequest = new BookingBookHotelRequest();
			$rules = $bookingBookRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();

			$filters = array_merge($filters, $request->all());		

			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->book($filters);
			}
			// TODO: Add other suppliers
		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | book ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);

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
	 *		   examples={
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
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function changeBooking(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->changeBooking($filters);
			}
			// TODO: Add other suppliers

		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | changeItems ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);
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
	 *        response=401,
	 *        description="Unauthenticated",
	 *    ),
	 *    @OA\Response(
	 *        response=403,
	 *        description="Forbidden"
	 *    ),
	 *    security={{ "apiAuth": {} }}
	 * )
	 */
	public function listBookings(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

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
	 *        response=401,
	 *        description="Unauthenticated",
	 *    ),
	 *    @OA\Response(
	 *        response=403,
	 *        description="Forbidden"
	 *    ),
	 *    security={{ "apiAuth": {} }}
	 * )
	 */
	public function retrieveBooking(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->retrieveBooking($filters);
			}
			// TODO: Add other suppliers

		} catch (Exception $e) {
			\Log::error('HotelBookingApiHandler | retrieveBooking ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
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
	 *    security={{ "apiAuth": {} }}
	 * )
	 */
		public function cancelBooking(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->cancelBooking($filters);
			}
			// TODO: Add other suppliers

		} catch (Exception $e) {
			\Log::error('HotelBookingApiHanlder | cancelBooking ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		if (isset($data['error'])) return $this->sendError($data['error']);

		return $this->sendResponse(['result' => $data['success']], 'success');
	}
}
