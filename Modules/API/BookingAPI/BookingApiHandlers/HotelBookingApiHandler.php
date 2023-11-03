<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Exception;
use Modules\API\BaseController;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Illuminate\Support\Facades\Validator;
use Modules\Inspector\SearchInspectorController;
use Modules\API\BookingAPI\ExpediaHotelBookingApiHandler;

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
	 *   description="Add an hotel room(s) to the cart.",
	 *    @OA\Parameter(
	 *      name="search_id",
	 *      in="query",
	 *      required=true,
	 *      description="Search ID",
	 *      example="86eec169-3cda-4275-9a15-e52251ff62c5"
	 *    ),
	 *    @OA\Parameter(
	 *      name="supplier",
	 *      in="query",
	 *      required=true,
	 *      description="Supplier",
	 *      example="Expedia"
	 *    ),
	 *    @OA\Parameter(
	 *      name="hotel_id",
	 *      in="query",
	 *      required=true,
	 *      description="Hotel ID",
	 *      example="36572902"
	 *    ),
	 *    @OA\Parameter(
	 *      name="room_id",
	 *      in="query",
	 *      required=true,
	 *      description="Room ID",
	 *      example="213307487"
	 *    ),
	 *    @OA\Parameter(
	 *      name="rate",
	 *      in="query",
	 *      required=true,
	 *      description="Rate",
	 *      example="242261024"
	 *    ),
	 *    @OA\Parameter(
	 *      name="bed_groups",
	 *      in="query",
	 *      required=true,
	 *      description="Bed groups",
	 *      example="37310"
	 *    ),
	 *    @OA\Parameter(
	 *      name="query",
	 *      in="query",
	 *      required=true,
	 *      description="Query parameters",
	 *      @OA\Schema(
	 *        type="object",
	 *        @OA\Property(
	 *          property="affiliate_reference_id",
	 *          type="string",
	 *          description="Affiliate reference ID",
	 *          example="4480A12"
	 *        ),
	 *        @OA\Property(
	 *          property="hold",
	 *          type="boolean",
	 *          description="Hold the booking",
	 *          example=false
	 *        ),
	 *        @OA\Property(
	 *          property="email",
	 *          type="string",
	 *          description="Email address",
	 *          example="john@example.com",
	 *          nullable=true
	 *        ),
	 *        @OA\Property(
	 *          property="phone",
	 *          type="object",
	 *          description="Phone number",
	 *          @OA\Property(
	 *            property="country_code",
	 *            type="string",
	 *            description="Country code",
	 *            example="1"
	 *          ),
	 *          @OA\Property(
	 *            property="area_code",
	 *            type="string",
	 *            description="Area code",
	 *            example="487"
	 *          ),
	 *          @OA\Property(
	 *            property="number",
	 *            type="string",
	 *            description="Phone number",
	 *            example="5550077"
	 *          )
	 *        ),
	 *        @OA\Property(
	 *          property="rooms",
	 *          type="array",
	 *          description="Rooms",
	 *          @OA\Items(
	 *            type="object",
	 *            @OA\Property(
	 *              property="given_name",
	 *              type="string",
	 *              description="Given name",
	 *              example="John"
	 *            ),
	 *            @OA\Property(
	 *              property="family_name",
	 *              type="string",
	 *              description="Family name",
	 *              example="Portman"
	 *            ),
	 *            @OA\Property(
	 *              property="smoking",
	 *              type="boolean",
	 *              description="Smoking",
	 *              example=false
	 *            )
	 *          )
	 *        ),
	 *        @OA\Property(
	 *          property="payments",
	 *          type="array",
	 *          description="Payments",
	 *          @OA\Items(
	 *            type="object",
	 *            @OA\Property(
	 *              property="type",
	 *              type="string",
	 *              description="Type",
	 *              example="affiliate_collect"
	 *            ),
	 *            @OA\Property(
	 *              property="billing_contact",
	 *              type="object",
	 *              description="Billing contact",
	 *              @OA\Property(
	 *                property="given_name",
	 *                type="string",
	 *                description="Given name",
	 *                example="John"
	 *              ),
	 *              @OA\Property(
	 *                property="family_name",
	 *                type="string",
	 *                description="Family name",
	 *                example="Smith"
	 *              ),
	 *              @OA\Property(
	 *                property="address",
	 *                type="object",
	 *                description="Address",
	 *                @OA\Property(
	 *                  property="line_1",
	 *                  type="string",
	 *                  description="Address line 1",
	 *                  example="555 1st St"
	 *                ),
	 *                @OA\Property(
	 *                  property="city",
	 *                  type="string",
	 *                  description="City",
	 *                  example="Seattle"
	 *                ),
	 *                @OA\Property(
	 *                  property="state_province_code",
	 *                  type="string",
	 *                  description="State/province code",
	 *                  example="WA"
	 *                ),
	 *                @OA\Property(
	 *                  property="postal_code",
	 *                  type="string",
	 *                  description="Postal code",
	 *                  example="98121"
	 *                ),
	 *                @OA\Property(
	 *                  property="country_code",
	 *                  type="string",
	 *                  description="Country code",
	 *                  example="US"
	 *                )
	 *              )
	 *            )
	 *          )
	 *        )
	 *      )
	 *    ),	  
	 *    @OA\RequestBody(
	 *      @OA\MediaType(
	 *        mediaType="application/json",
	 *        @OA\Schema(
	 *          @OA\Property(
	 *            property="query",
	 *            type="object",
	 *            @OA\Property(
	 *              property="affiliate_reference_id",
	 *              type="string",
	 *              description="Affiliate reference ID",
	 *              example="4480A12"
	 *            ),
	 *            @OA\Property(
	 *              property="hold",
	 *              type="boolean",
	 *              description="Hold the booking",
	 *              example=false
	 *            ),
	 *            @OA\Property(
	 *              property="email",
	 *              type="string",
	 *              description="Email address",
	 *              example="john@example.com",
	 *              nullable=true
	 *            ),
	 *            @OA\Property(
	 *              property="phone",
	 *              type="object",
	 *              description="Phone number",
	 *              @OA\Property(
	 *                property="country_code",
	 *                type="string",
	 *                description="Country code",
	 *                example="1"
	 *              ),
	 *              @OA\Property(
	 *                property="area_code",
	 *                type="string",
	 *                description="Area code",
	 *                example="487"
	 *              ),
	 *              @OA\Property(
	 *                property="number",
	 *                type="string",
	 *                description="Phone number",
	 *                example="5550077"
	 *              )
	 *            ),
	 *            @OA\Property(
	 *              property="rooms",
	 *              type="array",
	 *              description="Rooms",
	 *              @OA\Items(
	 *                type="object",
	 *                @OA\Property(
	 *                  property="given_name",
	 *                  type="string",
	 *                  description="Given name",
	 *                  example="John"
	 *                ),
	 *                @OA\Property(
	 *                  property="family_name",
	 *                  type="string",
	 *                  description="Family name",
	 *                  example="Portman"
	 *                ),
	 *                @OA\Property(
	 *                  property="smoking",
	 *                  type="boolean",
	 *                  description="Smoking",
	 *                  example=false
	 *                )
	 *              )
	 *            ),
	 *            @OA\Property(
	 *              property="payments",
	 *              type="array",
	 *              description="Payments",
	 *              @OA\Items(
	 *                type="object",
	 *                @OA\Property(
	 *                  property="type",
	 *                  type="string",
	 *                  description="Type",
	 *                  example="affiliate_collect"
	 *                ),
	 *                @OA\Property(
	 *                  property="billing_contact",
	 *                  type="object",
	 *                  description="Billing contact",
	 *                  @OA\Property(
	 *                    property="given_name",
	 *                    type="string",
	 *                    description="Given name",	
	 *                    example="John"
	 *                  ),
	 *                  @OA\Property(
	 *                    property="family_name",
	 *                    type="string",
	 *                    description="Family name",
	 *                    example="Smith"
	 *                  ),
	 *                  @OA\Property(
	 *                    property="address",
	 *                    type="object",
	 *                    description="Address",
	 *                    @OA\Property(
	 *                      property="line_1",
	 *                      type="string",
	 *                      description="Address line 1",
	 *                      example="555 1st St"
	 *                    ),
	 *                    @OA\Property(
	 *                      property="city",
	 *                      type="string",
	 *                      description="City",
	 *                      example="Seattle"
	 *                    ),
	 *                    @OA\Property(
	 *                      property="state_province_code",
	 *                      type="string",
	 *                      description="State/province code",
	 *                      example="WA"
	 *                    ),
	 *                    @OA\Property(
	 *                      property="postal_code",
	 *                      type="string",
	 *                      description="Postal code",
	 *                      example="98121"
	 *                    ),
	 *                    @OA\Property(
	 *                      property="country_code",
	 *                      type="string",
	 *                      description="Country code",
	 *                      example="US"
	 *                    )
	 *                  )
	 *               )
	 *             )    
	 *           )
	 *         )
	 *       )
	 *     )
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *   ),
	 *   @OA\Response(
	 *       response=401,
	 *       description="Unauthenticated",
	 *   ),
	 *   @OA\Response(
	 *       response=403,
	 *       description="Forbidden"
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
			$filters = array_merge($filters, $request->all());

			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
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
	 *   path="api/booking/retrieve-items",
	 *   summary="Get detailed information about a hotel.",
	 *   description="Get detailed information about a hotel.",
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
	 *    @OA\Parameter(
	 *      name="room_id",
	 *      in="query",
	 *      required=true,
	 *      description="Room ID",
	 *      @OA\Schema(
	 *        type="string",
	 *        example="213307487"
	 *      )
	 *    ),
	 *    @OA\Parameter(
	 *      name="query",
	 *      in="query",
	 *      required=true,
	 *      description="Query parameters",
	 *      @OA\Schema(
	 *        type="object",
	 *        @OA\Property(
	 *          property="given_name",
	 *          type="string",
	 *          description="Given name",
	 *          example="John",
	 *          @OA\Schema(
	 *            type="string",
	 *            example="John"
	 *          )
	 *        ),
	 *        @OA\Property(
	 *          property="family_name",
	 *          type="string",
	 *          description="Family name",
	 *          example="Smith",
	 *          @OA\Schema(
	 *            type="string",
	 *            example="Smith"
	 *          )
	 *        ),
	 *        @OA\Property(
	 *          property="smoking",
	 *          type="boolean",
	 *          description="Smoking",
	 *          example=false,
	 *          @OA\Schema(
	 *            type="boolean",
	 *            example=false
	 *          )
	 *        ),
	 *        @OA\Property(
	 *          property="special_request",
	 *          type="string",
	 *          description="Special request",
	 *          example="Top floor or away from street please",
	 *          @OA\Schema(
	 *            type="string",
	 *            example="Top floor or away from street please"
	 *          )
	 *        ),
	 *        @OA\Property(
	 *          property="loyalty_id",
	 *          type="string",
	 *          description="Loyalty ID",
	 *          example="ABC123",
	 *          @OA\Schema(
	 *            type="string",
	 *            example="ABC123"
	 *          )
	 *        )
	 *      )
	 *    ),	  
	 *    @OA\RequestBody(
	 *      @OA\MediaType(
	 *        mediaType="application/json",
	 *        @OA\Schema(
	 *          @OA\Property(
	 *            property="query",
	 *            type="object",
	 *            @OA\Property(
	 *              property="given_name",
	 *              type="string",
	 *              description="Given name",
	 *              example="John",
	 *              @OA\Schema(
	 *                type="string",
	 *                example="John"
	 *              )
	 *            ),
	 *            @OA\Property(
	 *              property="family_name",
	 *              type="string",
	 *              description="Family name",
	 *              example="Smith",
	 *              @OA\Schema(
	 *                type="string",
	 *                example="Smith"
	 *              )
	 *            ),
	 *            @OA\Property(
	 *              property="smoking",
	 *              type="boolean",
	 *              description="Smoking",
	 *              example=false,
	 *              @OA\Schema(
	 *                type="boolean",
	 *                example=false
	 *              )
	 *            ),
	 *            @OA\Property(
	 *              property="special_request",
	 *              type="string",
	 *              description="Special request",
	 *              example="Top floor or away from street please",
	 *              @OA\Schema(
	 *                type="string",
	 *                example="Top floor or away from street please"
	 *              )
	 *            ),
	 *            @OA\Property(
	 *              property="loyalty_id",
	 *              type="string",
	 *              description="Loyalty ID",
	 *              example="ABC123",
	 *              @OA\Schema(
	 *                type="string",
	 *                example="ABC123"
	 *              )
	 *            )
	 *          )
	 *        )
	 *      )
	 *    ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *   ),
	 *   @OA\Response(
	 *       response=401,
	 *       description="Unauthenticated",
	 *   ),
	 *   @OA\Response(
	 *       response=403,
	 *       description="Forbidden"
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
			\Log::error('HotelBookingApiHandler | listBookings ' . $e->getMessage());
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
	 *   path="api/booking/list-bookings",
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
	/**
	 * @OA\Delete(
	 *   tags={"Booking API"},
	 *   path="api/booking/remove-item",
	 *   summary="Delete an item from the cart.",
	 *   description="Delete an item from the cart.",
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
