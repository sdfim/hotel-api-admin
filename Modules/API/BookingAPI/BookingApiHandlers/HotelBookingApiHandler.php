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
use Illuminate\Support\Facades\Validator;
use Modules\API\Requests\BookingAddPassengersHotelRequest as AddPassengersRequest;
use Modules\API\Requests\BookingRemoveItemHotelRequest;
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
	 * HotelBookingApiHandler constructor.
	 */
	public function __construct()
	{
		$this->apiInspector = new SearchInspectorController();
		$this->expedia = new ExpediaHotelBookingApiHandler();
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
		$validate = Validator::make($request->all(), (new BookingAddItemHotelRequest())->rules());
        if ($validate->fails()) return $this->sendError($validate->errors());
		
		$filters = $request->all();
		$data = [];
		try {
			if (request()->has('booking_id')) {

				if (ApiBookingInspector::isBook(request()->get('booking_id'), request()->get('booking_item'))) {
					return $this->sendError([
						'error' => 'booking_id - this cart is not available',
						'message' => 'This cart is at the booking stage or beyond.'
					]);
				}

				if (ApiBookingInspector::isDuplicate(request()->get('booking_id'), request()->get('booking_item'))) {
					return $this->sendError([
						'error' => 'booking_item, booking_id pair is not unique.', 
						'message' => 'This item is already in your cart.'
					]);
				}

				$filters['booking_id'] = request()->get('booking_id');
			}

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
		$validate = Validator::make($request->all(), (new BookingRemoveItemHotelRequest())->rules());
        if ($validate->fails()) return $this->sendError($validate->errors());

		$filters = $request->all();
		dd($filters);
		$data = [];
		try {
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
	 *     @OA\Parameter(
	 *       name="booking_item",
	 *       in="query",
	 *       required=true,
	 *       description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
	 *       In the response object for each rate is a **booking_item** property.",
	 *       example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
	 *     ),
	 *     @OA\RequestBody(
	 *     description="JSON object containing the details of the reservation.",
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
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function addPassengers(Request $request, string $supplier): JsonResponse
	{
		$filters = Validator::make($request->all(), (new AddPassengersRequest())->rules());
        if ($filters->fails()) return $this->sendError($filters->errors());

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

		if (isset($data['error'])) return $this->sendError($data['error']);
		return $this->sendResponse(['result' => $data['success']], 'success');
	}

}
