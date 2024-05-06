<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\BaseController;
use Modules\API\BookingAPI\Controllers\BookingApiHandlerInterface;
use Modules\API\BookingAPI\Controllers\ExpediaHotelBookingApiController;
use Modules\API\BookingAPI\Controllers\HbsiHotelBookingApiController;
use Modules\API\Suppliers\HbsiSupplier\HbsiService;
use Modules\Enums\SupplierNameEnum;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @OA\PathItem(
 * path="/api/booking",
 * )
 */
class HotelBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
    /**
     * @param ExpediaHotelBookingApiController $expedia
     * @param HbsiHotelBookingApiController $hbsi
     */
    public function __construct(
        private readonly ExpediaHotelBookingApiController $expedia = new ExpediaHotelBookingApiController(),
        private readonly HbsiHotelBookingApiController    $hbsi = new HbsiHotelBookingApiController(),
        private readonly HbsiService                      $hbsiService = new HbsiService(),

    )
    {
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addItem(Request $request, string $supplier): JsonResponse
    {
        $filters = $request->all();
        $data = [];
        try {
            if (request()->has('booking_id')) {

                if (BookingRepository::isBook($request->booking_id, $request->booking_item)) {
                    return $this->sendError(
                        'booking_id - this cart is not available. This cart is at the booking stage or beyond.',
                    );
                }

                if (BookingRepository::isDuplicate($request->booking_id, $request->booking_item)) {
                    return $this->sendError('booking_item, booking_id pair is not unique. This item is already in your cart.');
                }

                $filters['booking_id'] = $request->booking_id;
            }

            if (SupplierNameEnum::from($supplier) === SupplierNameEnum::HBSI
                && Cache::get('room_combinations:' . $request->booking_item)) {
                $this->hbsiService->updateBookingItemsData($request->booking_item);
            }

            if (!ApiBookingItemRepository::isComlete($request->booking_item)) {
                return $this->sendError('booking_item - this item is single');
            }

            $apiBookingItem = ApiBookingItem::where('booking_item', $request->booking_item)->first()->toArray();
            $booking_item_data = json_decode($apiBookingItem['booking_item_data'], true);
            $filters['search_id'] = $apiBookingItem['search_id'];

            $filters = array_merge($filters, $request->all());

            if (SupplierNameEnum::from($supplier) === SupplierNameEnum::EXPEDIA) {
                $filters['hotel_id'] = $booking_item_data['hotel_id'];
                $filters['room_id'] = $booking_item_data['room_id'];
                $filters['rate'] = $booking_item_data['rate'];
                $filters['bed_groups'] = $booking_item_data['bed_groups'];

                $data = $this->expedia->addItem($filters);
            }

            if (SupplierNameEnum::from($supplier) === SupplierNameEnum::HBSI) {
                $filters['hotel_id'] = $booking_item_data['hotel_id'];
                $filters['hotel_supplier_id'] = $booking_item_data['hotel_supplier_id'];
                $filters['room_id'] = $booking_item_data['room_id'];
                $filters['rate_ordinal'] = $booking_item_data['rate_ordinal'];
                $filters['rate_type'] = $booking_item_data['rate_type'];
                $filters['rate_occupancy'] = $booking_item_data['rate_occupancy'];

                $data = $this->hbsi->addItem($filters);
            }

        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelBookingApiHandler | addItem ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->sendError($e->getMessage(), 'failed');
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
     *           examples={
     *             "example1": @OA\Schema(ref="#/components/examples/BookingRemoveItemResponse", example="BookingRemoveItemResponse"),
     *         },
     *      )
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
    public function removeItem(Request $request, string $supplier): JsonResponse
    {
        $filters = $request->all();
        try {
            $data = match (SupplierNameEnum::from($supplier)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->removeItem($filters),
                SupplierNameEnum::HBSI => $this->hbsi->removeItem($filters),
                default => [],
            };

        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | removeItem ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['error'])) return $this->sendError($data['error']);

        return $this->sendResponse(['result' => $data['success']], 'success');
    }
}
