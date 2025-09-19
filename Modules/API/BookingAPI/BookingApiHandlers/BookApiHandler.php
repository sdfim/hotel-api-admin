<?php

declare(strict_types=1);

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Jobs\ClearSearchCacheByBookingItemsJob;
use App\Jobs\RetrieveBookingJob;
use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use App\Models\ApiSearchInspector;
use App\Models\Reservation;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingInspectorRepository as BookRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use App\Repositories\ChannelRepository;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BaseController;
use Modules\API\BookingAPI\Controllers\ExpediaBookApiController;
use Modules\API\BookingAPI\Controllers\HbsiBookApiController;
use Modules\API\BookingAPI\Controllers\HotelTraderBookApiController;
use Modules\API\Requests\BookingAddPassengersHotelRequest as AddPassengersRequest;
use Modules\API\Requests\BookingAvailabileEndpointsChangeBookHotelRequest;
use Modules\API\Requests\BookingAvailabilityChangeBookHotelRequest;
use Modules\API\Requests\BookingBookRequest;
use Modules\API\Requests\BookingCancelBooking;
use Modules\API\Requests\BookingChangeHardBookHotelRequest;
use Modules\API\Requests\BookingChangeSoftBookHotelRequest;
use Modules\API\Requests\BookingPriceCheckBookHotelRequest;
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
    private const AGE_ADULT = 18;

    public function __construct(
        private readonly ExpediaBookApiController $expedia,
        private readonly HbsiBookApiController $hbsi,
        private readonly HotelTraderBookApiController $hTrader,
    ) {}

    /**
     * @throws GuzzleException
     */
    public function book(BookingBookRequest $request): JsonResponse
    {
        Log::info("BOOK ACTION - START - $request->booking_id"); // $request->booking_id
        $sts = microtime(true);

        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            Log::info("BOOK ACTION - END - $request->booking_id", ['error' => $determinant['error']]); // $request->booking_id

            return response()->json(['error' => $determinant['error']], 400);
        }

        $filters = $request->all();

        $items = BookRepository::notBookedItems($request->booking_id);

        if (! $items->count()) {
            Log::info("BOOK ACTION - END - $request->booking_id", ['error' => 'No items to book OR the order cart (booking_id) is complete/booked']); // $request->booking_id

            return $this->sendError('No items to book OR the order cart (booking_id) is complete/booked', 'failed');
        }

        if (isset($request->special_requests)) {
            $arrItems = $items->pluck('booking_item')->toArray();
            foreach ($request->special_requests as $item) {
                if (! in_array($item['booking_item'], $arrItems)) {
                    Log::info("BOOK ACTION - END - $request->booking_id", ['error' => 'special_requests must be in valid booking_item']); // $request->booking_id

                    return $this->sendError('special_requests must be in valid booking_item. '.
                        'Valid booking_items: '.implode(',', $arrItems), 'failed');
                }
            }
        }

        $data = [];
        Log::debug('BookApiHandler book items: '.$items);

        foreach ($items as $item) {
            Log::debug('BookApiHandler book LOOP item: '.$item);
            $type = $item->search_type;
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first();
                $supplierName = SupplierNameEnum::from($supplier->name);

                $data[] = match ([$supplierName, $type]) {
                    [SupplierNameEnum::EXPEDIA, TypeRequestEnum::HOTEL->value] => $this->expedia->book($filters, $item),
                    [SupplierNameEnum::HBSI, TypeRequestEnum::HOTEL->value] => $this->hbsi->book($filters, $item),
                    [SupplierNameEnum::HOTEL_TRADER, TypeRequestEnum::HOTEL->value] => $this->hTrader->book($filters, $item),
                    default => [],
                };
            } catch (Exception $e) {
                Log::error('BookApiHandler | book '.$e->getMessage());
                Log::error($e->getTraceAsString());
                $data[] = [
                    'booking_id' => $item->booking_id,
                    'booking_item' => $item->booking_item,
                    'search_id' => $item->search_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $totalTime = (microtime(true) - $sts).' seconds';

        foreach ($data as $item) {
            if (isset($item['error'])) {
                Log::info("BOOK ACTION - END - $request->booking_id", ['time' => $totalTime, 'error' => $item['error']]); // $request->booking_id

                return $this->sendError($item);
            }

            if (isset($item['Error'])) {
                Log::info("BOOK ACTION - END - $request->booking_id", ['time' => $totalTime, 'error' => $item['Error']]); // $request->booking_id

                return $this->sendError($item);
            }
        }

        /**
         * Based on these booking_items, all cached pricing search responses will be determined and this cache will be cleared.
         * This prevents the possibility of booking an already booked booking_item.
         */
        $itemsToDeleteFromCache = BookRepository::bookedBookingItems($request->booking_id);
        ClearSearchCacheByBookingItemsJob::dispatchSync($itemsToDeleteFromCache);

        // Retrieve booking to get the full details after booking
        RetrieveBookingJob::dispatch($request->booking_id);

        $totalTime = (microtime(true) - $sts).' seconds';

        Log::info("BOOK ACTION - END - $request->booking_id", ['time' => $totalTime]); // $request->booking_id

        return $this->sendResponse($data, 'success');
    }

    public function availableEndpoints(BookingAvailabileEndpointsChangeBookHotelRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        $supplierId = ApiBookingItem::where('booking_item', $request->booking_item)->first()->supplier_id;
        $supplierMach = $supplier = SupplierNameEnum::from(Supplier::where('id', $supplierId)->first()->name);

        $isNonRefundable = ApiBookingItemRepository::isNonRefundable($request->booking_item);
        if ($isNonRefundable) {
            $supplierMach = 'NonRefundable';
        }

        $endpointDetails = [
            'soft-change' => [
                'name' => 'Soft Change',
                'description' => 'Endpoint to handle soft changes in booking.',
                'url' => 'api/booking/change/soft-change',
            ],
            'availability' => [
                'name' => 'Availability Check',
                'description' => 'Endpoint to check booking availability.',
                'url' => 'api/booking/change/availability',
            ],
            'price-check' => [
                'name' => 'Price Check',
                'description' => 'Endpoint to check the price of bookings.',
                'url' => 'api/booking/change/price-check',
            ],
            'hard-change' => [
                'name' => 'Hard Change',
                'description' => 'Endpoint to handle hard changes in booking.',
                'url' => 'api/booking/change/hard-change',
            ],
        ];

        $endpoints = match ($supplierMach) {
            SupplierNameEnum::HBSI => ['soft-change', 'availability', 'price-check', 'hard-change'],
            SupplierNameEnum::HOTEL_TRADER => ['soft-change', 'availability', 'price-check', 'hard-change'],
            SupplierNameEnum::EXPEDIA, 'NonRefundable' => ['soft-change'],
            default => [],
        };

        $result = [];
        foreach ($endpoints as $endpoint) {
            if (isset($endpointDetails[$endpoint])) {
                $result[] = $endpointDetails[$endpoint];
            }
        }

        return $this->sendResponse([
            'booking_item' => $request->booking_item,
            'non_refundable' => $isNonRefundable,
            'supplier' => $supplier,
            'endpoints' => $result,
        ], 'success');
    }

    /**
     * Change soft booking for hotel.
     * Possible to change data passengers, special requests, and other booking details only.
     *
     * @throws GuzzleException
     */
    public function changeSoftBooking(BookingChangeSoftBookHotelRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        if (! BookRepository::isBook($request->booking_id, $request->booking_item)) {
            return $this->sendError('booking_id and/or booking_item not yet booked', 'failed');
        }
        $filters = $request->all();

        if (isset($filters['special_requests'])) {
            foreach ($filters['special_requests'] as $k => $item) {
                $filters['special_requests'][$k]['booking_item'] = $request->booking_item;
            }
        }

        $supplierId = ApiBookingItem::where('booking_item', $request->booking_item)->first()->supplier_id;
        $supplier = SupplierNameEnum::from(Supplier::where('id', $supplierId)->first()->name);

        if ($supplier === SupplierNameEnum::HBSI) {
            $passengersReq = &$filters['passengers'];
            $passengersData = ApiBookingInspectorRepository::getChangePassengers($filters['booking_id'], $filters['booking_item']);
            $passengersData = json_decode($passengersData->request, true);
            $guests = $passengersData['rooms'];
            if (count(Arr::collapse($guests)) != count($passengersReq)) {
                return $this->sendError('Incorrect number of passengers', 'failed');
            }

            $pIndex = 0;
            foreach ($passengersData['passengers'] as $passenger) {
                $bItem = Arr::first($passenger['booking_items'] ?? [], fn ($value) => $value['booking_item'] == $request->booking_item);
                if (! isset($passenger['booking_items']) || $bItem) {
                    $passengersReq[$pIndex]['date_of_birth'] = $passenger['date_of_birth'];
                    $pIndex++;
                }
            }
        }

        $this->saveChangePassengers($filters, $supplierId);

        try {
            $data = match ($supplier) {
                SupplierNameEnum::EXPEDIA => $this->expedia->changeSoftBooking($filters),
                SupplierNameEnum::HBSI => $this->hbsi->changeBooking($filters),
                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->changeBooking($filters),
                default => [],
            };
        } catch (Exception $e) {
            Log::error('BookApiHandler | changeItems '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['errors'])) {
            return $this->sendError($data['errors'], $data['message']);
        }

        return $this->sendResponse($data ?? [], 'success');
    }

    private function saveChangePassengers(array $filters, int $supplierId): void
    {
        $passengers = $filters['passengers'];
        $bookingId = $filters['booking_id'];
        $bookingItem = $filters['booking_item'];
        $bookingItemInspector = ApiBookingInspector::where('booking_id', $bookingId)
            ->where('booking_item', $filters['booking_item'])
            ->where('type', 'change_passengers');

        if ($bookingItemInspector->exists()) {
            $bookingItemInspector->delete();
            $status = 'Update change passengers';
            $subType = 'update_change';
        } else {
            $status = 'Change passengers';
            $subType = 'change';
        }

        foreach ($passengers as &$passenger) {
            $passenger['booking_items'] = [['room' => $passenger['room'], 'booking_item' => $bookingItem]];
        }

        if (isset($filters['search_id']) && ! empty($filters['search_id'])) {
            $searchId = $filters['search_id'];
        } else {
            $apiBookingInspector = ApiBookingInspector::where('booking_id', $bookingId)
                ->where('booking_item', $bookingItem)->first();
            $searchId = $apiBookingInspector->search_id;
        }

        $apiSearchInspector = ApiSearchInspector::where('search_id', $searchId)->first()->request;
        $countRooms = count(json_decode($apiSearchInspector, true)['occupancy']);

        $passengersData = $this->dtoAddPassengers(['passengers' => $passengers])[$bookingItem];
        for ($i = 1; $i <= $countRooms; $i++) {
            if (array_key_exists($i, $passengersData['rooms'])) {
                $filters['rooms'][] = $passengersData['rooms'][$i]['passengers'];
            }
        }

        $filters['search_id'] = $searchId;

        $bookingInspector = BookingRepository::newBookingInspector([
            $bookingId, $filters, $supplierId, 'change_passengers', $subType, 'hotel',
        ]);

        SaveBookingInspector::dispatchSync($bookingInspector, [], [
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
            'status' => $status,
        ]);
    }

    public function changeHardBooking(BookingChangeHardBookHotelRequest $request): JsonResponse
    {
        //        if (ApiBookingItemRepository::isNonRefundable($request->booking_item)) {
        //            return $this->sendError('This booking_item is non-refundable', 'failed');
        //        }

        if (! BookRepository::exists($request->booking_id, $request->booking_item)) {
            return $this->sendError('the pair booking_id and booking_item is not correct ', 'failed');
        }

        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        if (! BookRepository::isBook($request->booking_id, $request->booking_item)) {
            return $this->sendError('booking_id and/or booking_item not yet booked', 'failed');
        }

        if (! ApiBookingInspector::where('booking_id', $request->booking_id)
            ->where('booking_item', $request->booking_item)
            ->where('type', 'price-check')
            ->where('status', 'success')->exists()) {
            return $this->sendError('First you need to do change/price-check', 'failed');
        }

        $filters = $request->all();
        $apiBookingItem = ApiBookingItem::where('booking_item', $request->booking_item)->first();

        if (isset($filters['special_requests'])) {
            foreach ($filters['special_requests'] as $k => $item) {
                $filters['special_requests'][$k]['booking_item'] = $request->booking_item;
            }
        }

        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['new_booking_item']);

        $this->saveChangePassengers($filters, $apiBookingItem->supplier_id);

        try {
            $data = match (SupplierNameEnum::from($apiBookingItem->supplier->name)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->changeSoftBooking($filters),
                SupplierNameEnum::HBSI => $this->hbsi->changeBooking($filters, 'hard'),
                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->changeBooking($filters, 'hard'),
                default => [],
            };
        } catch (Exception $e) {
            Log::error('BookApiHandler | changeItems '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['errors'])) {
            return $this->sendError($data['errors'], $data['message']);
        }

        return $this->sendResponse($data ?? [], 'success');
    }

    public function availabilityChange(BookingAvailabilityChangeBookHotelRequest $request): JsonResponse
    {
        //        if (ApiBookingItemRepository::isNonRefundable($request->booking_item)) {
        //            return $this->sendError('This booking_item is non-refundable', 'failed');
        //        }

        if (! BookRepository::exists($request->booking_id, $request->booking_item)) {
            return $this->sendError('the pair booking_id and booking_item is not correct ', 'failed');
        }

        $determinant = $this->determinant($request, false);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        if (! BookRepository::isBook($request->booking_id, $request->booking_item)) {
            return $this->sendError('booking_id and/or booking_item not yet booked', 'failed');
        }

        $filters = $request->all();

        $bookingItem = ApiBookingItem::with('supplier')
            ->where('booking_item', $request->booking_item)->first();

        $search_id = $bookingItem->search_id;

        $firstQuery = ApiSearchInspector::where('search_id', $search_id)->first()->request;

        $filters = array_merge($filters, json_decode($firstQuery, true));

        if ($request->has('checkin') && $request->has('checkout')) {
            $filters['checkin'] = $request->checkin;
            $filters['checkout'] = $request->checkout;
        }

        if ($request->has('occupancy')) {
            $filters['occupancy'] = $request->occupancy;
        }

        try {
            $data = match (SupplierNameEnum::from($bookingItem->supplier->name)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->availabilityChange($filters),
                SupplierNameEnum::HBSI => $this->hbsi->availabilityChange($filters),
                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->availabilityChange($filters),
                default => [],
            };
        } catch (Exception $e) {
            Log::error('BookApiHandler | changeItems '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['errors'])) {
            return $this->sendError($data['errors'], $data['message']);
        }

        $result = [
            'query' => $filters,
            'result' => $data ? Arr::get($data, 'result') : [],
            'change_search_id' => $data ? Arr::get($data, 'change_search_id') : '',
        ];

        return $this->sendResponse($result, 'success');
    }

    public function priceCheck(BookingPriceCheckBookHotelRequest $request): JsonResponse
    {
        //        if (ApiBookingItemRepository::isNonRefundable($request->booking_item)) {
        //            return $this->sendError('This booking_item is non-refundable', 'failed');
        //        }

        if (! BookRepository::exists($request->booking_id, $request->booking_item)) {
            return $this->sendError('the pair booking_id and booking_item is not correct ', 'failed');
        }

        $determinant = $this->determinant($request, false);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        if (! BookRepository::isBook($request->booking_id, $request->booking_item)) {
            return $this->sendError('booking_id and/or booking_item not yet booked', 'failed');
        }
        $filters = $request->all();

        $bookingItem = ApiBookingItem::with('supplier')
            ->where('booking_item', $request->booking_item)->first();

        try {
            $data = match (SupplierNameEnum::from($bookingItem->supplier->name)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->priceCheck($filters),
                SupplierNameEnum::HBSI => $this->hbsi->priceCheck($filters),
                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->priceCheck($filters),
                default => [],
            };
        } catch (Exception $e) {
            Log::error('BookApiHandler | priceCheck '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['errors'])) {
            return $this->sendError($data['errors'], $data['message']);
        }

        $result = [
            'result' => $data ? Arr::get($data, 'result') : [],
        ];

        return $this->sendResponse($result, 'success');
    }

    public function listBookings(ListBookingsRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        $latestIds = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'retrieve')
            ->where('status', 'success')
            ->groupBy('booking_id', 'booking_item')
            ->selectRaw('MAX(id) as id')
            ->orderByDesc('id')
            ->pluck('id');

        $retrieved = ApiBookingInspector::whereIn('id', $latestIds)->get();

        $tokenId = ChannelRepository::getTokenId(request()->bearerToken());
        $apiClientId = data_get($request->all(), 'api_client_id');
        $apiClientEmail = data_get($request->all(), 'api_client_email');

        $bookingDateFrom = $request->input('booking_date_from');
        $bookingDateTo = $request->input('booking_date_to');

        $itemsBookedByApiClient = ApiBookingInspector::query()
            ->where('token_id', $tokenId)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->when(filled($apiClientId) || filled($apiClientEmail), function ($q) use ($apiClientId, $apiClientEmail) {
                $q->where(function ($query) use ($apiClientId, $apiClientEmail) {
                    if (filled($apiClientId)) {
                        $query->orWhereJsonContains('request->api_client->id', (string) $apiClientId);
                    }
                    if (filled($apiClientEmail)) {
                        $query->orWhereJsonContains('request->api_client->email', (string) $apiClientEmail);
                    }
                });
            })
            ->when(filled($bookingDateFrom), function ($q) use ($bookingDateFrom) {
                $q->whereDate('created_at', '>=', $bookingDateFrom);
            })
            ->when(filled($bookingDateTo), function ($q) use ($bookingDateTo) {
                $q->whereDate('created_at', '<=', $bookingDateTo);
            })
            ->has('metadata')
            ->orderBy('created_at', 'desc')
            ->pluck('booking_item');

        $data = [];
        foreach ($retrieved as $item) {
            $disk = config('filesystems.default', 's3');
            if (! Storage::disk($disk)->exists($item->client_response_path)) {
                continue;
            }
            $json = json_decode(Storage::disk($disk)->get($item->client_response_path), true);
            if (! $json) {
                continue;
            }
            if (! in_array($item->booking_item, $itemsBookedByApiClient->toArray())) {
                continue;
            }

            $data[] = $json;
        }

        $totalCount = count($data);
        $page = (int) $request->input('page', 1);
        $resultsPerPage = (int) $request->input('results_per_page', 10);
        $offset = ($page - 1) * $resultsPerPage;
        $paginatedData = array_slice($data, $offset, $resultsPerPage);

        return $this->sendResponse([
            'count' => $totalCount,
            'page' => $page,
            'results_per_page' => $resultsPerPage,
            'result' => $paginatedData,
        ], 'success');
    }

    public function listBookingsOld(ListBookingsRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        $suppliers = SupplierNameEnum::pricingList();

        $data = [];
        foreach ($suppliers as $supplier) {
            try {
                $bookings = match ($supplier) {
                    SupplierNameEnum::EXPEDIA->value => $this->expedia->listBookings(),
                    SupplierNameEnum::HBSI->value => $this->hbsi->listBookings(),
                    SupplierNameEnum::HOTEL_TRADER->value => $this->hTrader->listBookings(),
                };
                if (is_array($bookings)) {
                    $data = array_merge($data, $bookings);
                }
            } catch (Exception $e) {
                Log::error('HotelBookingApiHandler | listBookings for '.$supplier->value.' '.$e->getMessage());
                Log::error($e->getTraceAsString());
                // Optionally, add error info to the result for this supplier
                $data[] = [
                    'supplier' => $supplier->value,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');
    }

    /**
     * @throws GuzzleException
     */
    public function retrieveBooking(BookingRetrieveItemsRequest $request): JsonResponse
    {
        $filters = $request->all();

        $itemsBooked = ApiBookingsMetadataRepository::bookedItems($request->booking_id);

        $data = [];
        $retrieved = [];

        foreach ($itemsBooked as $item) {
            if (in_array($item->booking_item, $retrieved)) {
                continue;
            } else {
                $retrieved[] = $item->booking_item;
            }
            try {
                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;
                $data[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->retrieveBooking($filters, $item),
                    SupplierNameEnum::HBSI => $this->hbsi->retrieveBooking($filters, $item),
                    SupplierNameEnum::HOTEL_TRADER => $this->hTrader->retrieveBooking($filters, $item),
                    default => [],
                };
            } catch (Exception $e) {
                Log::error('BookApiHandler | retrieveBooking '.$e->getMessage());
                Log::error($e->getTraceAsString());
                $data[] = [
                    'booking_id' => $item->booking_id,
                    'booking_item' => $item->booking_item,
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
     * @throws GuzzleException
     */
    public function cancelBooking(BookingCancelBooking $request): JsonResponse
    {
        $determinant = $this->determinant($request, false);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        if (isset($request->booking_item)) {
            $itemsBooked = ApiBookingsMetadataRepository::bookedItem($request->booking_id, $request->booking_item);
        } else {
            $itemsBooked = ApiBookingsMetadataRepository::bookedItems($request->booking_id);
        }

        $filters = $request->all();
        $data = [];
        $canceled = [];
        foreach ($itemsBooked as $item) {
            if (! BookRepository::isBook($request->booking_id, $item->booking_item, false)) {
                $data[] = ['error' => 'booking_id and/or booking_item not yet booked'];

                continue;
            }

            if (in_array($item->booking_item, $canceled)) {
                continue;
            } else {
                $canceled[] = $item->booking_item;
            }

            try {
                $filters['search_id'] = ApiBookingItem::where('booking_item', $item->booking_item)->first()?->search_id;
                $filters['booking_item'] = $item->booking_item;
                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;
                $response = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->cancelBooking($filters, $item),
                    SupplierNameEnum::HBSI => $this->hbsi->cancelBooking($filters, $item),
                    SupplierNameEnum::HOTEL_TRADER => $this->hTrader->cancelBooking($filters, $item),
                    default => [],
                };
                $data[] = $response;

                // If cancellation is successful, update Reservation
                if (! isset($response['error']) && ! isset($response['Error'])) {
                    Reservation::where('booking_id', $item->booking_id)
                        ->where('booking_item', $item->booking_item)
                        ->whereNull('canceled_at')
                        ->update(['canceled_at' => now()]);
                }
            } catch (Exception $e) {
                Log::error('BookApiHandler | cancelBooking '.$e->getMessage());
                Log::error($e->getTraceAsString());
                $data[] = [
                    'booking_id' => $item->booking_id,
                    'booking_item' => $item->booking_item,
                    'error' => $e->getMessage(),
                ];
            }
        }
        if (empty($data)) {
            return $this->sendError('booking_id not yet booked', 'failed');
        }

        $errors = [];

        foreach ($data as $item) {
            if (isset($item['Error'])) {
                $errors[] = $item['Error'];
            }
            if (isset($item['error'])) {
                $errors[] = $item['error'];
            }
        }

        if (! empty($errors)) {
            // we need the 3-4 parameters to match force cancellation critirea in the admin crm
            return $this->sendError($errors, '', 400, $data);
        }

        // Retrieve booking to get the full details after booking
        RetrieveBookingJob::dispatch($request->booking_id);

        return $this->sendResponse(['result' => $data], 'success');
    }

    public function retrieveItems(BookingRetrieveBooking $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        $itemsInCart = BookRepository::getItemsInCart($request->booking_id);

        $res = [];
        try {
            foreach ($itemsInCart as $item) {

                if (BookRepository::isBook($request->booking_id, $item->booking_item)) {
                    continue;
                }

                $supplier = Supplier::where('id', $item->supplier_id)->first()->name;
                $res[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->retrieveItem($item),
                    SupplierNameEnum::HBSI => $this->hbsi->retrieveItem($item),
                    SupplierNameEnum::HOTEL_TRADER => $this->hTrader->retrieveItem($item),
                    default => [],
                };
            }
            if (empty($res)) {
                return $this->sendError('Cart is empty or booked', 'failed');
            }

        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | retrieveItems '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        return $this->sendResponse(['result' => $res], 'success');

    }

    public function addPassengers(AddPassengersRequest $request): JsonResponse
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }

        $filters = $request->all();
        $filtersOutput = $this->dtoAddPassengers($filters);
        $checkData = $this->checkCountGuestsChildrenAges($filtersOutput);
        if (! empty($checkData)) {
            return $this->sendError($checkData, 'failed');
        }

        $itemsInCart = BookRepository::getItemsInCart($request->booking_id);

        $bookingRequestItems = array_keys($filtersOutput);

        foreach ($bookingRequestItems as $requestItem) {
            if (! in_array($requestItem, $itemsInCart->pluck('booking_item')->toArray())) {
                return $this->sendError('This booking_item is not in the cart.', 'failed');
            }
        }

        try {
            $response = [];
            foreach ($bookingRequestItems as $booking_item) {
                if (BookRepository::isBook($request->booking_id, $booking_item)) {
                    return $this->sendError('Cart is empty or booked', 'failed');
                }
                $supplierId = ApiBookingItem::where('booking_item', $booking_item)->first()->supplier_id;
                $supplier = Supplier::where('id', $supplierId)->first()->name;

                $filters = $request->all();
                $filters['booking_item'] = $booking_item;

                $response[] = match (SupplierNameEnum::from($supplier)) {
                    SupplierNameEnum::EXPEDIA => $this->expedia->addPassengers($filters, $filtersOutput[$booking_item], SupplierNameEnum::EXPEDIA->value),
                    SupplierNameEnum::HBSI => $this->hbsi->addPassengers($filters, $filtersOutput[$booking_item], SupplierNameEnum::HBSI->value),
                    SupplierNameEnum::HOTEL_TRADER => $this->hTrader->addPassengers($filters, $filtersOutput[$booking_item], SupplierNameEnum::HOTEL_TRADER->value),
                    default => [],
                };
            }
        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | addPassengers '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        return $this->sendResponse(['result' => $response], 'success');
    }

    private function determinant(Request $request, bool $validateWithApiBookings = true): array
    {
        // This validation must remains here for the previous bookings with TravelTek (imported HBSI bookings)
        if (! $validateWithApiBookings) {
            $apiBooking = ApiBookingsMetadata::where('booking_id', $request->get('booking_id'));

            if ($request->has('booking_item')) {
                $apiBooking = $apiBooking->where('booking_item', $request->get('booking_item'));
            }

            if ($apiBooking->first() === null) {
                return ['error' => 'Invalid Booking'];
            }

            return [];
        }

        $requestTokenId = PersonalAccessToken::findToken($request->bearerToken())->id;

        // check Owner token
        if ($request->has('booking_item')) {
            if (! $this->validatedUuid('booking_item')) {
                return [];
            }
            $apiBookingItem = ApiBookingItem::where('booking_item', $request->booking_item)->with('search')->first();
            $cacheBookingItem = Cache::get('room_combinations:'.$request->booking_item);
            if (! $apiBookingItem && ! $cacheBookingItem) {
                return ['error' => 'Invalid booking_item'];
            }
            $dbTokenId = $apiBookingItem->search->token_id;
            if ($dbTokenId !== $requestTokenId) {
                return ['error' => 'Owner token not match'];
            }
        }

        // check Owner token
        if ($request->has('booking_id')) {

            if (! $this->validatedUuid('booking_id')) {
                return ['error' => 'Invalid booking_id'];
            }
            $waitTime = 0;
            $maxWaitTime = 5;
            $bi = null;
            while ($waitTime < $maxWaitTime) {
                $bi = BookRepository::geTypeSupplierByBookingId($request->booking_id);
                if (! empty($bi)) {
                    break;
                }
                Log::debug('Waiting for booking_id to be available '.$waitTime.' s');
                sleep(1);
                $waitTime++;
            }
            if (empty($bi)) {
                return ['error' => 'Invalid booking_id'];
            }
            $dbTokenId = $bi['token_id'];

            if ($dbTokenId !== $requestTokenId) {
                return ['error' => 'Owner token not match'];
            }
        }

        return [];
    }

    private function validatedUuid($id): bool
    {
        $validate = Validator::make(request()->all(), [$id => 'required|size:36']);
        if ($validate->fails()) {
            return false;
        }

        return true;
    }

    private function dtoAddPassengers(array $input): array
    {
        $output = [];
        foreach ($input['passengers'] as $passenger) {
            foreach ($passenger['booking_items'] as $booking) {
                $bookingItem = $booking['booking_item'];

                $age = null;
                if (! empty($passenger['date_of_birth'])) {
                    try {
                        $age = Carbon::parse($passenger['date_of_birth'])->age;
                    } catch (\Exception $e) {
                        $age = null;
                    }
                }

                // type hotel
                if (isset($booking['room'])) {
                    $room = $booking['room'];
                    if (isset($output[$bookingItem])) {
                        $output[$bookingItem]['rooms'][$room]['passengers'][] = [
                            'title' => $passenger['title'],
                            'given_name' => $passenger['given_name'],
                            'family_name' => $passenger['family_name'],
                            'date_of_birth' => $passenger['date_of_birth'],
                            'age' => $age,
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
                                            'date_of_birth' => $passenger['date_of_birth'],
                                            'age' => $age,
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                }
                // type flight
                if (! isset($booking['room'])) {
                    if (isset($output[$bookingItem])) {
                        $output[$bookingItem]['passengers'][] = [
                            'title' => $passenger['title'],
                            'given_name' => $passenger['given_name'],
                            'family_name' => $passenger['family_name'],
                            'date_of_birth' => $passenger['date_of_birth'],
                            'age' => $age,
                        ];
                    } else {
                        $output[$bookingItem] = [
                            'booking_item' => $bookingItem,
                            'passengers' => [
                                [
                                    'title' => $passenger['title'],
                                    'given_name' => $passenger['given_name'],
                                    'family_name' => $passenger['family_name'],
                                    'date_of_birth' => $passenger['date_of_birth'],
                                    'age' => $age,
                                ],
                            ],
                        ];
                    }
                }

            }
        }

        return $output;
    }

    /**
     * @return array|string[]
     */
    private function checkCountGuestsChildrenAges(array $filtersOutput): array
    {
        foreach ($filtersOutput as $bookingItem => $booking) {
            $search = ApiBookingItem::where('booking_item', $bookingItem)->first();

            if (! $search) {
                return ['booking_item' => 'Invalid booking_item'];
            }

            $type = ApiSearchInspector::where('search_id', $search->search_id)->first()->search_type;

            if (TypeRequestEnum::from($type) === TypeRequestEnum::FLIGHT) {
                continue;
            }
            if (TypeRequestEnum::from($type) === TypeRequestEnum::COMBO) {
                continue;
            }
            if (TypeRequestEnum::from($type) === TypeRequestEnum::HOTEL) {
                return $this->checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $search->search_id);
            }
        }

        return [];
    }

    private function checkCountGuestsChildrenAgesHotel($bookingItem, $booking, $searchId): array
    {
        $searchData = json_decode(ApiSearchInspector::where('search_id', $searchId)->first()->request, true);

        foreach ($booking['rooms'] as $room => $roomData) {

            $ages = [];
            foreach ($roomData['passengers'] as $passenger) {
                $dob = Carbon::parse($passenger['date_of_birth']);
                $now = Carbon::now();
                $ages[] = floor($now->diffInYears($dob, true));
            }

            $childrenCount = 0;
            $adultsCount = 0;
            foreach ($ages as $age) {
                if ($age < self::AGE_ADULT) {
                    $childrenCount++;
                } else {
                    $adultsCount++;
                }
            }

            if ($adultsCount != $searchData['occupancy'][$room - 1]['adults']) {
                return [
                    'type' => 'The number of adults not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'number_of_adults_in_search' => $searchData['occupancy'][$room - 1]['adults'],
                    'number_of_adults_in_query' => $adultsCount,
                ];
            }
            if (! isset($searchData['occupancy'][$room - 1]['children_ages']) && $childrenCount != 0) {
                return [
                    'type' => 'The number of children not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'number_of_children_in_search' => 0,
                    'number_of_children_in_query' => $childrenCount,
                ];
            }

            if (! isset($searchData['occupancy'][$room - 1]['children_ages'])) {
                continue;
            }

            if ($childrenCount != count($searchData['occupancy'][$room - 1]['children_ages'])) {
                return [
                    'type' => 'The number of children not match.',
                    'booking_item' => $bookingItem,
                    'search_id' => $searchId,
                    'room' => $room,
                    'number_of_children_in_search' => count($searchData['occupancy'][$room - 1]['children_ages']),
                    'number_of_children_in_query' => $childrenCount,
                ];
            }

            $childrenAges = $searchData['occupancy'][$room - 1]['children_ages'];
            sort($childrenAges);
            $childrenAgesInQuery = [];
            foreach ($roomData['passengers'] as $passenger) {
                $givenDate = Carbon::create($passenger['date_of_birth']);
                $currentDate = Carbon::now();
                $years = floor($givenDate->diffInYears($currentDate, true));
                if ($years >= self::AGE_ADULT) {
                    continue;
                }
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
                    'children_ages_in_query' => implode(',', $childrenAgesInQuery),
                ];
            }
        }

        return [];
    }
}
