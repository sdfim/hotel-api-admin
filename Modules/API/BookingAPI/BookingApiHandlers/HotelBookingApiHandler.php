<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Exception;
use Modules\API\BaseController;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
class HotelBookingApiHandler extends BaseController // implements BookingApiHandlerInterface
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
	 *   tags={"Booking API"},
	 *   path="/api/booking/add-item",
	 *   summary="Add an hotel room(s) to the cart.",
	 *   description="The **'/api/booking/add-item'** endpoint is a fundamental feature of a booking or reservation system. <br>
	 *   It enables users to augment their existing bookings by adding new items or services, enhancing the overall booking experience.",
	 *    @OA\Parameter(
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
	 *       ref="#/components/schemas/BookingAddItemRequest", 
	 *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingAddItemRequest", example="BookingAddItemRequest"),
     *       },
	 *     ),
	 *   ),
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
	 *   tags={"Booking API"},
	 *   path="/api/booking/remove-item",
	 *   summary="Delete an item from the cart.",
	 *   description="Delete an item from the cart.",
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
	 *      required=true,
	 *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
	 *      In the response object for each rate is a **booking_item** property.",
	 *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
	 *    ),
	 *    @OA\Response(
	 *      response=200,
	 *      description="OK",
	 *      @OA\JsonContent(
	 *        ref="#/components/schemas/BookingRemoveItemResponse",
	 *        examples={
	 *        "example1": @OA\Schema(ref="#/components/examples/BookingRemoveItemResponse", example="BookingRemoveItemResponse"),
	 *        }
	 *      )
	 *    ),
	 *    @OA\Response(
	 *      response=400,
	 *      description="Unauthenticated",
	 *      @OA\JsonContent(
	 *        examples={
	 *        "example1": @OA\Schema(ref="#/components/examples/BookingRemoveItemResponseError", example="BookingRemoveItemResponseError"),
	 *        },
	 *      )
	 *    ),
	 *    security={{ "apiAuth": {} }}
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
	 *   tags={"Booking API"},
	 *   path="/api/booking/retrieve-items",
	 *   summary="Get detailed information about a hotel.",
	 *   description="The **'/api/booking/retrieve-items'** endpoint is a critical feature within a booking or reservation system. <br>  Its primary purpose is to provide users with the ability to retrieve a comprehensive list of items or services <br>   that have been associated with a particular booking. <br>  This endpoint is essential for users to review the details and components of their reservations.",
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

		return $this->sendResponse(['result' => $data], 'success');
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
	 * @OA\Put(
	 *   tags={"Booking API"},
	 *   path="/api/booking/change-items",
	 *   summary="Change the details of a hotel room(s) in the cart.",
	 *   description="Change the details of a hotel room(s) in the cart.",
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
	 *       ref="#/components/schemas/BookingChangeItemRequest", 
	 *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingChangeItemRequest", example="BookingAddItemRequest"),
     *       },
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/BookingChangeItemResponse", 
	 *		   examples={
	 *             "example1": @OA\Schema(ref="#/components/examples/BookingChangeItemResponse", example="BookingChangeItemResponse"),
     *         },
	 *     )
	 *   ),
	 *   @OA\Response(
	 *     response=400,
	 *     description="Bad Request",
	 *     @OA\JsonContent(
	 *       examples={
	 *         "example1": @OA\Schema(ref="#/components/examples/BookingChangeItemResponseError", example="BookingChangeItemResponseError"),
	 *       },
	 *     )
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function changeItems(Request $request, string $supplier): JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->changeItems($filters);
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
	public function book(Request $request, string $supplier): JsonResponse
	{
	}

	/**
	 * @param Request $request
	 * @param string $supplier
	 * @return JsonResponse
	 */
	/**
	 * @OA\Get(
	 *   tags={"Booking API"},
	 *   path="/api/booking/list-bookings",
	 *   summary="Get detailed information about a bookings.",
	 *   description="Get detailed information about a bookings.",
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
	public function retrieveBooking(Request $request, string $supplier): JsonResponse
	{
	}

	/**
	 * @param Request $request
	 * @param string $supplier
	 * @return JsonResponse
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
			\Log::error('HotelBookingApiHanlder | removeItem ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}

		if (isset($data['error'])) return $this->sendError($data['error']);

		return $this->sendResponse(['result' => $data['success']], 'success');
	}
}
