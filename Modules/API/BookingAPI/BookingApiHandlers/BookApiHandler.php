<?php
declare(strict_types=1);

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookRepository;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BaseController;
use Modules\API\BookingAPI\Controllers\ExpediaBookApiController;
use Modules\API\BookingAPI\Controllers\HbsiBookApiController;
use Modules\API\Requests\BookingAddPassengersHotelRequest as AddPassengersRequest;
use Modules\API\Requests\BookingBookRequest;
use Modules\API\Requests\BookingCancelBooking;
use Modules\API\Requests\BookingChangeBookHotelRequest;
use Modules\API\Requests\BookingRetrieveBooking;
use Modules\API\Requests\BookingRetrieveItemsRequest;
use Modules\API\Requests\ListBookingsRequest;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

/**
 * @OA\PathItem(
 * path="/api/booking",
 * )
 */
class BookApiHandler extends BaseController
{
    private const AGE_ADULT = 16;

    /**
     * @param ExpediaBookApiController $expedia
     * @param HbsiBookApiController $hbsi
     */
    public function __construct(
        private readonly ExpediaBookApiController $expedia,
        private readonly HbsiBookApiController    $hbsi
    )
    {
    }

    /**
     * @param BookingBookRequest $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function book(BookingBookRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        $filters = $request->all();

        $items = BookRepository::notBookedItems($request->booking_id);

        if (!$items->count()) {
            return $this->sendError('No items to book OR the order cart (booking_id) is complete/booked', 'failed');
        }

        if (isset($request->special_requests)) {
            $arrItems = $items->pluck('booking_item')->toArray();
            foreach ($request->special_requests as $item) {
                if (!in_array($item['booking_item'], $arrItems)) {
                    return $this->sendError('special_requests must be in valid booking_item. ' .
                        'Valid booking_items: ' . implode(',', $arrItems), 'failed');
                }
            }
        }

        $data = [];
        Log::debug('BookApiHandler book items: ' . $items);
        foreach ($items as $item) {
            Log::debug('BookApiHandler book LOOP item: ' . $item);
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first();
                $supplierName = SupplierNameEnum::from($supplier->name);
                $data[] = match ($supplierName) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->book($filters, $item),
                    SupplierNameEnum::HBSI => $this->hbsi->book($filters, $item),
                    default => [],
                };
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

            if (isset($item['Error'])) {
                return $this->sendError($item);
            }
        }

        return $this->sendResponse($data, 'success');
    }

    /**
     * @param BookingChangeBookHotelRequest $request
     * @return JsonResponse
     */
    public function changeBooking(BookingChangeBookHotelRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        if (!BookRepository::isBook($request->booking_id, $request->booking_item)) {
            return $this->sendError('booking_id and/or booking_item not yet booked', 'failed');
        }
        $filters = $request->all();

        $supplierId = ApiBookingItem::where('booking_item', $request->booking_item)->first()->supplier_id;
        $supplier = Supplier::where('id', $supplierId)->first()->name;

        try {
            $data = match (SupplierNameEnum::from($supplier)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->changeBooking($filters),
                SupplierNameEnum::HBSI => $this->hbsi->changeBooking($filters),
                default => [],
            };
        } catch (Exception $e) {
            Log::error('BookApiHandler | changeItems ' . $e->getMessage());
            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);

        return $this->sendResponse($data ?? [], 'success');
    }

    /**
     * @param ListBookingsRequest $request
     * @return JsonResponse
     */
        public function listBookings(ListBookingsRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        try {
            $data = match (SupplierNameEnum::from($request->supplier)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->listBookings(),
                SupplierNameEnum::HBSI => $this->hbsi->listBookings(),
                default => [],
            };
        } catch (Exception $e) {
            Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
            return $this->sendError($e->getMessage(), 'failed');
        }

        return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');
    }

    /**
     * @param BookingRetrieveItemsRequest $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function retrieveBooking(BookingRetrieveItemsRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        $itemsBooked = BookRepository::bookedItems($request->booking_id);
        $data = [];
        foreach ($itemsBooked as $item) {
            if (!BookRepository::isBook($request->booking_id, $item->booking_item)) {
                $data[] = ['error' => 'booking_id and/or booking_item not yet booked'];
                continue;
            }
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;
                $data[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->retrieveBooking($filters, $item),
                    SupplierNameEnum::HBSI => $this->hbsi->retrieveBooking($filters, $item),
                    default => [],
                };
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
            return $this->sendError('booking_id not yet booked', 'failed');
        }

        return $this->sendResponse(['result' => $data], 'success');
    }

    /**
     * @param BookingCancelBooking $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function cancelBooking(BookingCancelBooking $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        if (isset($request->booking_item)) {
            $itemsBooked = BookRepository::bookedItem($request->booking_id, $request->booking_item);
        } else {
            $itemsBooked = BookRepository::bookedItems($request->booking_id);
        }

        $filters = $request->all();
        $data = [];
        foreach ($itemsBooked as $item) {
            if (!BookRepository::isBook($request->booking_id, $item->booking_item)) {
                $data[] = ['error' => 'booking_id and/or booking_item not yet booked'];
                continue;
            }
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;
                $data[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->cancelBooking($filters, $item),
                    SupplierNameEnum::HBSI => $this->hbsi->cancelBooking($filters, $item),
                    default => [],
                };

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
            return $this->sendError('booking_id not yet booked', 'failed');
        }

        return $this->sendResponse(['result' => $data], 'success');
    }

    /**
     * @param BookingRetrieveBooking $request
     * @return JsonResponse
     */
    public function retrieveItems(BookingRetrieveBooking $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        $itemsInCart = BookRepository::getItemsInCart($request->booking_id);

        $res = [];
        try {
            foreach ($itemsInCart as $item) {

                if (BookRepository::isBook($request->booking_id, $item->booking_item)) continue;

                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;
                $res[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->retrieveItem($item),
                    SupplierNameEnum::HBSI => $this->hbsi->retrieveItem($item),
                    default => [],
                };
            }
            if (empty($res)) {
                return $this->sendError('Cart is empty or booked', 'failed');
            }

        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | retrieveItems ' . $e->getMessage());
            return $this->sendError($e->getMessage(), 'failed');
        }

        return $this->sendResponse(['result' => $res], 'success');

    }

    /**
     * @param AddPassengersRequest $request
     * @return JsonResponse
     */
    public function addPassengers(AddPassengersRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['error' => $determinant['error']], 400);

        $filters = $request->all();
        $filtersOutput = $this->dtoAddPassengers($filters);
        $checkData = $this->checkCountGuestsChildrenAges($filtersOutput);
        if (!empty($checkData)) return $this->sendError($checkData, 'failed');

        $itemsInCart = BookRepository::getItemsInCart($request->booking_id);

        $bookingRequestItems = array_keys($filtersOutput);
        foreach ($bookingRequestItems as $requestItem) {
            if (!in_array($requestItem, $itemsInCart->pluck('booking_item')->toArray()))
                return $this->sendError('This booking_item is not in the cart.', 'failed');
        }

        try {
            $res = [];
            $occupiedRooms = [];

            foreach ($bookingRequestItems as $booking_item) {

                if (BookRepository::isBook($request->booking_id, $booking_item)) {
                    return $this->sendError('Cart is empty or booked', 'failed');
                }
                $supplierId = ApiBookingItem::where('booking_item', $booking_item)->first()->supplier_id;
                $supplier = Supplier::where('id', $supplierId)->first()->name;

                $filters = $request->all();
                $filters['booking_item'] = $booking_item;

                $res[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->addPassengers($filters, $filtersOutput[$booking_item], SupplierNameEnum::EXPEDIA->value),
                    SupplierNameEnum::HBSI => $this->hbsi->addPassengers($filters, $filtersOutput[$booking_item], SupplierNameEnum::HBSI->value),
                    default => [],
                };
            }
        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | addPassengers ' . $e->getMessage());
            return $this->sendError($e->getMessage(), 'failed');
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

        # check Owner token
        if ($request->has('booking_item')) {
            if (!$this->validatedUuid('booking_item')) return [];
            $apiBookingItem = ApiBookingItem::where('booking_item', $request->booking_item)->with('search')->first();
            $cacheBookingItem = Cache::get('room_combinations:' . $request->booking_item);
            if (!$apiBookingItem && !$cacheBookingItem ) return ['error' => 'Invalid booking_item'];
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
    private function validatedUuid($id): bool
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
    private function dtoAddPassengers(array $input): array
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
    private function checkCountGuestsChildrenAges(array $filtersOutput): array
    {
        foreach ($filtersOutput as $bookingItem => $booking) {
            $search = ApiBookingItem::where('booking_item', $bookingItem)->first();

            if (!$search) return ['booking_item' => 'Invalid booking_item'];

            $type = ApiSearchInspector::where('search_id', $search->search_id)->first()->search_type;

            if (TypeRequestEnum::from($type) === TypeRequestEnum::FLIGHT) continue;
            if (TypeRequestEnum::from($type) === TypeRequestEnum::COMBO) continue;
            if (TypeRequestEnum::from($type) === TypeRequestEnum::HOTEL) return $this->checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $search->search_id);
        }

        return [];
    }

    /**
     * @param $bookingItem
     * @param $booking
     * @param $searchId
     * @return array
     */
    private function checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $searchId): array
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
                    'children_ages_in_search' => implode(',', $childrenAges),
                    'children_ages_in_query' => implode(',', $childrenAgesInQuery)
                ];
            }
        }
        return [];
    }
}
