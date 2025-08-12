<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\MoveBookingItemCache;
use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveBookingItems;
use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveReservations;
use App\Jobs\SaveSearchInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use App\Models\ApiBookingsMetadata;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use App\Repositories\ApiSearchInspectorRepository;
use App\Repositories\ChannelRepository;
use App\Repositories\HotelTraderContentRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\HotelTraderSupplier\HotelTraderClient;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderHotelBookTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderHotelPricingTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderiHotelBookingRetrieveBookingTransformer;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;
use Throwable;

class HotelTraderBookApiController extends BaseBookApiController
{
    public function __construct(
        private readonly HotelTraderClient $hotelTraderClient,
        private readonly HotelTraderHotelBookTransformer $hotelTraderHotelBookTransformer,
        private readonly HotelTraderHotelPricingTransformer $hotelTraderHotelPricingTransformer,
        private readonly PricingRulesTools $pricingRulesService,
    ) {}

    public function book(array $filters, ApiBookingInspector $bookingInspector): ?array
    {
        $booking_id = $bookingInspector->booking_id;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;

        Log::info("BOOK ACTION - HotelTrader - $booking_id", ['filters' => $filters]); // $booking_id

        $passengers = BookingRepository::getPassengers($booking_id, $filters['booking_item']);

        if (! $passengers) {
            Log::info("BOOK ACTION - ERROR - HotelTrader - $booking_id", ['error' => 'Passengers not found', 'filters' => $filters]); // $booking_id

            return [
                'error' => 'Passengers not found.',
                'booking_item' => $filters['booking_item'],
            ];
        } else {
            $passengersArr = $passengers->toArray();
            $dataPassengers = json_decode($passengersArr['request'], true);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;
        $inspectorBook = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'create', $bookingInspector->search_type,
        ]);

        $error = true;
        try {
            Log::info('HotelTraderBookApiController | book | '.json_encode($filters));
            Log::info("BOOK ACTION - REQUEST TO HotelTrader START - HotelTrader - $booking_id", ['filters' => $filters]); // $booking_id
            $sts = microtime(true);
            $bookingData = $this->hotelTraderClient->book($filters, $inspectorBook);
            Log::info("BOOK ACTION - REQUEST TO HotelTrader FINISH - HotelTrader - $booking_id", ['time' => (microtime(true) - $sts).' seconds', 'filters' => $filters]); // $booking_id

            $dataResponseToSave['original'] = [
                'request' => $bookingData['request'],
                'response' => $bookingData['response'],
                'main_guest' => $bookingData['main_guest'],
            ];
            if (Arr::get($bookingData, 'response')) {
                // Save Booking Info
                $this->saveBookingInfo($filters, $bookingData, $bookingData['main_guest']);

                $clientResponse = $this->hotelTraderHotelBookTransformer
                    ->toHotelBookResponseModel($filters, ['htConfirmationCode' => Arr::get($bookingData, 'response.htConfirmationCode')]);

                $error = false;
            } else {
                $clientResponse = Arr::get($bookingData, 'response.errors', []);
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HOTEL_TRADER->value;
            }

        } catch (RequestException $e) {
            Log::info("BOOK ACTION - ERROR - HotelTrader - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id
            Log::error('HotelTraderBookApiController | book | RequestException '.$e->getResponse()->getBody());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($inspectorBook, [], [], 'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]);

            return [
                'error' => 'Request Error. '.$e->getResponse()->getBody(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HOTEL_TRADER->value,
            ];
        } catch (\Exception $e) {
            Log::info("BOOK ACTION - ERROR - HotelTrader - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id
            Log::error('HotelTraderBookApiController | book | Exception '.$e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($inspectorBook, [], [], 'error',
                ['side' => 'app', 'message' => $e->getMessage()]);

            return [
                'error' => 'Unexpected Error. '.$e->getMessage(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HOTEL_TRADER->value,
            ];
        }

        if (! $error) {
            SaveBookingInspector::dispatch($inspectorBook, $dataResponseToSave, $clientResponse);
            // Save Book data to Reservation
            SaveReservations::dispatch($booking_id, $filters, $dataPassengers);
        }

        if (! $bookingData) {
            Log::info("BOOK ACTION - ERROR - HotelTrader - $booking_id", ['error' => 'Empty dataResponse', 'filters' => $filters]); // $booking_id

            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = $bookingData;
        } elseif ($error) {
            $res = $clientResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        return $res;
    }

    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): ?array
    {
        $booking_id = $filters['booking_id'];
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;
        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'retrieve', $apiBookingsMetadata->search_type,
        ]);

        $retrieveData = $this->hotelTraderClient->retrieve(
            $apiBookingsMetadata,
            $bookingInspector
        );

        $dataResponseToSave['original'] = [
            'request' => $retrieveData['request'],
            'response' => $retrieveData['response'],
        ];

        $clientDataResponse = Arr::get($retrieveData, 'response') ?
            HotelTraderiHotelBookingRetrieveBookingTransformer::RetrieveBookingToHotelBookResponseModel(Arr::get($retrieveData, 'response'))
            : Arr::get($retrieveData, 'errors');

        SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientDataResponse);

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return Arr::get($retrieveData, 'response');
        } else {
            return $clientDataResponse;
        }
    }

    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, int $iterations = 0): ?array
    {
        $booking_id = $filters['booking_id'];

        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;
        $inspectorCansel = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'cancel_booking', 'true', 'hotel',
        ]);

        try {
            $canceleData = $this->hotelTraderClient->cancel(
                $apiBookingsMetadata,
                $inspectorCansel
            );

            $dataResponseToSave['original'] = [
                'request' => $canceleData['request'],
                'response' => $canceleData['response'],
            ];

            if (Arr::get($canceleData, 'errors')) {
                $res = Arr::get($canceleData, 'errors');
            } else {
                $res = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'status' => 'Room canceled.',
                ];

                SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res);
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'status' => $message,
                'Error' => $message,
            ];

            $dataResponseToSave = is_array($message) ? $message : [];

            SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res, 'error',
                ['side' => 'app', 'message' => $message]);
        }

        return $res;
    }

    // TODO: Refactor this method to use the new HotelTraderClient
    public function listBookings(): ?array
    {
        $token_id = ChannelRepository::getTokenId(request()->bearerToken());
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $itemsBooked = ApiBookingInspector::where('token_id', $token_id)
            ->where('supplier_id', $supplierId)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->distinct()
            ->get();

        $filters['booking_id'] = request()->get('booking_id');
        $filters['supplier_data'] = request()->get('supplier_data') ?? false;
        $data = [];
        foreach ($itemsBooked as $item) {
            $data[] = $this->retrieveBooking($filters, $item);
        }

        return $data;
    }

    public function changeBooking(array $filters, string $mode = 'soft'): ?array
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;

        $bookingInspector = BookingRepository::newBookingInspector([
            $filters['booking_id'], $filters, $supplierId, 'change_book', 'change-'.$mode, 'hotel',
        ]);

        try {
            // Валидация для hard change
            if ($mode === 'hard') {
                $isNonRefundable = ApiBookingItemRepository::isNonRefundable($filters['booking_item']);
                if ($isNonRefundable) {
                    $clientResponse = [
                        'Errors' => ['This booking is non-refundable and cannot be hard-modified.'],
                        'booking_item' => $filters['booking_item'],
                        'supplier' => SupplierNameEnum::HOTEL_TRADER->value,
                    ];
                    SaveBookingInspector::dispatch($bookingInspector, [], $clientResponse, 'error', [
                        'side' => 'validation',
                        'message' => 'Attempted hard change for non-refundable booking.',
                    ]);

                    return $clientResponse;
                }
            }

            // Подготовка данных
            if ($mode === 'soft') {
                $modifyData = $this->prepareSoftChangeData($filters);
            } elseif ($mode === 'hard') {
                $modifyData = $this->prepareHardChangeData($filters);
            } else {
                throw new InvalidArgumentException("Unsupported change mode: $mode");
            }

            // Вызов клиента
            $result = $this->hotelTraderClient->modifyBooking($modifyData, $bookingInspector);

            $response = $result['response'] ?? [];
            $errors = $result['errors'] ?? [];
            $mainGuest = Arr::get($result, 'main_guest');

            $dataResponseToSave = [
                'original' => [
                    'request' => $result['request'],
                    'response' => $response,
                    'main_guest' => $mainGuest,
                ],
            ];

            if (! empty($errors)) {
                $clientResponse = $errors;
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HOTEL_TRADER->value;

                SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientResponse, 'error');

                return $clientResponse;
            }

            // Трансформация и сохранение
            $clientResponse = $this->hotelTraderHotelBookTransformer->toHotelBookResponseModel($filters);
            SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientResponse);

            $apiBookingsMetadata = ApiBookingsMetadataRepository::getBookedItem($filters['booking_id'], $filters['booking_item']);
            $data = [
                ...$apiBookingsMetadata->booking_item_data,
                'main_guest' => Arr::get(json_decode($mainGuest, true), 'PersonName', []),
            ];
            ApiBookingsMetadataRepository::updateBookingItemData($apiBookingsMetadata, $data);

            return ['status' => 'Booking changed.'];

        } catch (RequestException|GuzzleException $e) {
            $message = $e->getResponse()?->getBody()?->getContents() ?? $e->getMessage();
            Log::error('HotelTraderBookApiController | changeBooking '.$message);
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($bookingInspector, [], [], 'error', [
                'side' => 'app',
                'message' => $message,
            ]);

            return ['Errors' => [$message]];

        } catch (Exception $e) {
            Log::error('HotelTraderBookApiController | changeBooking '.$e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($bookingInspector, [], [], 'error', [
                'side' => 'app',
                'message' => $e->getMessage(),
            ]);

            return ['Errors' => [$e->getMessage()]];
        }
    }

    protected function prepareSoftChangeData(array $filters): array
    {
        $meta = ApiBookingsMetadataRepository::getBookedItem(
            $filters['booking_id'],
            $filters['booking_item']
        );

        $htCode = $meta->supplier_booking_item_id;

        // guests по комнатам
        $guestsByRoom = [];
        foreach (($filters['passengers'] ?? []) as $p) {
            $room = (int) ($p['room'] ?? 1);

            // Определяем primary только для первого пассажира в каждой комнате
            $isPrimary = empty($guestsByRoom[$room]);

            $guestsByRoom[$room][] = [
                'firstName' => $p['given_name'] ?? '',
                'lastName' => $p['family_name'] ?? '',
                'email' => $p['email'] ?? 'test@hoteltrader.com',
                'adult' => true,
                'age' => $p['age'] ?? 30,
                'phone' => $p['phone'] ?? '1234567890',
                'primary' => $isPrimary,
            ];
        }

        // special_requests по комнатам
        $srByRoom = [];
        foreach (($filters['special_requests'] ?? []) as $sr) {
            $room = (int) ($sr['room'] ?? 1);
            $srByRoom[$room][] = $sr['special_request'];
        }

        // Индексы всех комнат, где что-то меняем
        $roomsIdx = array_unique(array_merge(array_keys($guestsByRoom), array_keys($srByRoom))) ?: [1];

        $rooms = [];
        foreach ($roomsIdx as $room) {
            $roomData = [
                'clientRoomConfirmationCode' => $filters['booking_item'].($room > 1 ? "-$room" : ''),
                'htRoomConfirmationCode' => $htCode.($room > 1 ? "-$room" : ''),
                'status' => 'MODIFY',
            ];

            if (! empty($guestsByRoom[$room])) {
                $roomData['guests'] = $guestsByRoom[$room];
            }

            if (! empty($srByRoom[$room])) {
                $roomData['roomSpecialRequests'] = $srByRoom[$room];
            }

            $rooms[] = $roomData;
        }

        return [
            'htConfirmationCode' => $htCode,
            'clientConfirmationCode' => $filters['booking_item'],
            'otaConfirmationCode' => $filters['booking_item'],
            'otaClientName' => 'htrader',
            'specialRequests' => [], // глобальных запросов нет
            'rooms' => $rooms,
        ];
    }

    protected function prepareHardChangeData(array $filters): array
    {
        // 1) базовые идентификаторы
        $meta = ApiBookingsMetadataRepository::getBookedItem($filters['booking_id'], $filters['booking_item']);
        $htConfirmationCode = $meta->supplier_booking_item_id;         // "HT-XXXXXX"
        $htRoomCodeOld = $meta->supplier_booking_room_id ?? null;  // если есть

        // 2) новые данные из new_booking_item (кэш/репо после priceCheck)
        $newData = ApiBookingItemRepository::getItemData($filters['new_booking_item']) ?? [];
        $htIdentifier = $newData['htIdentifier'] ?? null;    // обязателен для смены типа/рейта
        $rates = $newData['rate'] ?? null;            // { netPrice, tax, grossPrice, payAtProperty, dailyPrice[], dailyTax[] }

        // 3) occupancy: из search_id (из запроса), иначе из связанного поиска
        $searchId = $filters['search_id']
            ?? ApiBookingItemRepository::getSearchId($filters['new_booking_item'])
            ?? ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $searchReq = ApiSearchInspector::where('search_id', $searchId)->value('request');
        $search = $searchReq ? json_decode($searchReq, true) : [];
        $o0 = $search['occupancy'][0] ?? [];
        $occupancy = [
            'numberOfAdults' => (int) ($o0['adults'] ?? 2),
            'numberOfChildren' => isset($o0['children_ages']) ? count($o0['children_ages']) : 0,
            'childrenAges' => isset($o0['children_ages']) ? implode(',', $o0['children_ages']) : null,
        ];

        // 4) гости: берём последних сохранённых пассажиров по booking_item; primary = только первый
        $pass = BookingRepository::getPassengers($filters['booking_id'], $filters['booking_item']);
        $roomsSaved = $pass ? (json_decode($pass->request, true)['rooms'] ?? []) : [];
        $srcGuests = $roomsSaved[0] ?? []; // первая комната — как и раньше
        $guests = [];
        foreach ($srcGuests as $idx => $g) {
            $guests[] = [
                'firstName' => $g['given_name'] ?? 'Guest',
                'lastName' => $g['family_name'] ?? 'Name',
                'email' => $g['email'] ?? 'test@hoteltrader.com',
                'adult' => ($g['age'] ?? 30) >= 18,
                'age' => $g['age'] ?? 30,
                'phone' => $g['phone'] ?? '1234567890',
                'primary' => $idx === 0, // <-- только первый true
            ];
        }
        if (! $guests) {
            $guests = [[
                'firstName' => 'Test', 'lastName' => 'Guest', 'email' => 'test@hoteltrader.com',
                'adult' => true, 'age' => 30, 'phone' => '1234567890', 'primary' => true,
            ]];
        }

        // 5) собираем единственный room с статусом MODIFY — «замена» без CANCEL
        $room = array_filter([
            'htIdentifier' => $htIdentifier,                         // новый номер/рейтаплан
            'htRoomConfirmationCode' => $htRoomCodeOld,                         // если есть
            'clientRoomConfirmationCode' => $filters['booking_item'],               // твой client code для старой комнаты
            'roomSpecialRequests' => ['rate/roomtype replacement via hard-change'],
            'rates' => $rates,
            'occupancy' => $occupancy,
            'guests' => $guests,
            'status' => 'MODIFY',
        ], fn ($v) => $v !== null && $v !== []);

        return [
            'htConfirmationCode' => $htConfirmationCode,
            'clientConfirmationCode' => $filters['booking_item'],
            'otaConfirmationCode' => $filters['booking_item'],
            'otaClientName' => 'htrader',
            'specialRequests' => [],           // при необходимости можешь прокинуть общие
            'rooms' => [$room],
        ];
    }

    public function availabilityChange(array $filters): ?array
    {
        $bookingItemCode = $filters['booking_item'] ?? null;
        $bookingItem = ApiBookingItem::where('booking_item', $bookingItemCode)->first();
        $searchId = (string) Str::uuid();
        $hotelGiataId = Arr::get(json_decode($bookingItem->booking_item_data, true), 'hotel_id');
        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;
        $searchInspector = ApiSearchInspectorRepository::newSearchInspector([
            $searchId, $filters, [$supplierId], 'change', 'hotel',
        ]);

        $response = $this->priceByHotel($hotelGiataId, $filters, $searchInspector);

        $handled = $this->handlePriceHotelTrader(
            $response,
            $filters,
            $searchId,
            $this->pricingRulesService->rules($filters, [$hotelGiataId]),
            $this->pricingRulesService->rules($filters, [$hotelGiataId], true),
            [$hotelGiataId]
        );

        $clientResponse = $handled['clientResponse'];

        SaveSearchInspector::dispatch(
            $searchInspector,
            $handled['dataOriginal'] ?? [],
            [
                'count' => $handled['countResponse'],
                'query' => $filters,
                'results' => $handled['dataResponse'],
            ],
            [
                'count' => $handled['countClientResponse'],
                'total_pages' => max($handled['totalPages']),
                'query' => $filters,
                'results' => $clientResponse,
            ]
        );

        if (! empty($handled['bookingItems'])) {
            foreach ($handled['bookingItems'] as $items) {
                SaveBookingItems::dispatch($items);
            }
        }

        return [
            'result' => $clientResponse[SupplierNameEnum::HOTEL_TRADER->value] ?? [],
            'change_search_id' => $searchId,
        ];
    }

    public function priceCheck(array $filters): ?array
    {
        if (isset($filters['new_booking_item']) && Cache::get('room_combinations:'.$filters['new_booking_item'])) {
            $hotelService = new HotelCombinationService(SupplierNameEnum::HOTEL_TRADER->value);
            $hotelService->updateBookingItemsData($filters['new_booking_item'], true);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $filters['booking_id'], $filters, $supplierId, 'price-check', '', 'hotel',
        ]);

        $item = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $itemPrice = json_decode($item->booking_pricing_data, true);
        $totalPrice = $itemPrice['total_price'] ?? 0;

        $itemNew = ApiBookingItemCache::where('booking_item', $filters['new_booking_item'])->first();
        $itemPriceNew = json_decode($itemNew->booking_pricing_data, true);
        $totalPriceNew = $itemPriceNew['total_price'] ?? 0;

        $data['result']['incremental_total_price'] = $totalPriceNew - $totalPrice;

        $hotelierBookingReference = ApiBookingsMetadata::where('booking_id', $filters['booking_id'])
            ->where('booking_item', $filters['booking_item'])
            ->first()?->supplier_booking_item_id;

        $data['result']['current_booking_item'] = $this->getCurrentBookingItem($itemPrice);
        $data['result']['current_booking_item']['booking_item'] = $filters['booking_item'];
        $data['result']['current_booking_item']['hotelier_booking_reference'] = $hotelierBookingReference;

        $data['result']['new_booking_item'] = $this->getCurrentBookingItem($itemPriceNew);
        $data['result']['new_booking_item']['booking_item'] = $filters['booking_item'];

        MoveBookingItemCache::dispatchSync($itemNew->booking_item);
        SaveBookingInspector::dispatchSync($bookingInspector, [], $data);

        return $data;
    }

    // TODO: Refactor this method to use the new HotelTraderClient
    private function getCurrentBookingItem(array $itemPrice): array
    {
        return [
            'total_net' => $itemPrice['total_net'] ?? 0,
            'total_tax' => $itemPrice['total_tax'] ?? 0,
            'total_fees' => $itemPrice['total_fees'] ?? 0,
            'total_price' => $itemPrice['total_price'] ?? 0,
            'cancellation_policies' => $itemPrice['cancellation_policies'] ?? [],
            'breakdown' => $itemPrice['breakdown'] ?? [],
            'rate_name' => $itemPrice['rate_name'] ?? '',
            'room_name' => $itemPrice['room_type'] ?? '',
            'currency' => $itemPrice['currency'] ?? '',
        ];
    }

    private function priceByHotel(string $hotelId, array $filters, array $searchInspector): ?array
    {
        try {
            // 1) GIATA -> список supplier propertyIds (HotelTrader)
            $hotelIds = HotelTraderContentRepository::getIdsByGiataIds([$hotelId]) ?? [];
            $hotelIds = array_values(array_map('strval', $hotelIds));

            if (empty($hotelIds)) {
                return [
                    'original' => ['request' => [], 'response' => []],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            // 2) Вызов HotelTrader GraphQL
            $raw = $this->hotelTraderClient->availability($hotelIds, $filters, $searchInspector);

            // Ошибки от клиента — вернём их в совместимом виде
            if (! empty($raw['errors'])) {
                return [
                    'error' => $raw['errors'],
                    'original' => ['request' => $raw['request'] ?? [], 'response' => $raw['response'] ?? []],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            // 3) Собираем giata-контекст (имя отеля по желанию; null-safe)
            $details = HotelTraderContentRepository::getDetailByGiataId($hotelId);
            $giataName = $details->first()?->name ?? '';
            $giataContext = [
                'giata_id' => $hotelId,
                'name' => $giataName,
            ];

            // 4) Нормализация ответа под вход трансформера
            $properties = $raw['response'] ?? [];
            $normalized = $this->normalizeHotelTraderGraphQl($properties, $giataContext);

            // 5) Вернём «привычный» каркас
            return [
                'original' => ['request' => $raw['request'] ?? [], 'response' => $properties],
                'array' => $normalized,
                'total_pages' => 1,
            ];
        } catch (GuzzleException $e) {
            Log::error('HotelTrader priceByHotel GuzzleException '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => ['request' => [], 'response' => []],
                'array' => [],
                'total_pages' => 0,
            ];
        } catch (Throwable $e) {
            Log::error('HotelTrader priceByHotel Exception '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => ['request' => [], 'response' => []],
                'array' => [],
                'total_pages' => 0,
            ];
        }
    }

    private function normalizeHotelTraderGraphQl(array $properties, array $giata): array
    {
        return array_map(function (array $p) use ($giata) {
            $rooms = $p['rooms'] ?? [];

            // если пришёл плоский список ставок — заворачиваем в одну группу
            if (! empty($rooms) && ! isset(($rooms[0] ?? [])['rates'])) {
                $rooms = [['rates' => $rooms]];
            }

            return [
                'giata_id' => $giata['giata_id'] ?? null,
                'hotel_name' => $giata['name'] ?? '',
                'propertyId' => $p['propertyId'] ?? null,
                'city' => $p['city'] ?? null,
                'starRating' => $p['starRating'] ?? null,
                'occupancies' => $p['occupancies'] ?? [],
                'rooms' => $rooms,
            ];
        }, $properties);
    }

    private function handlePriceHotelTrader($supplierResponse, array $filters, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIgs): array
    {
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $countResponse = 0;
        $countClientResponse = 0;

        $hotelTraderResponse = $supplierResponse ?? ['array' => [], 'original' => [], 'total_pages' => 0];

        $supplierName = SupplierNameEnum::HOTEL_TRADER->value;
        $dataResponse[$supplierName] = $hotelTraderResponse['array'] ?? [];
        $dataOriginal[$supplierName] = $hotelTraderResponse['original'] ?? [];

        $st = microtime(true);
        $hotelGenerator = $this->hotelTraderHotelPricingTransformer->HotelTraderToHotelResponse(
            $dataResponse[$supplierName],
            $filters,
            $search_id,
            $pricingRules,
            $pricingExclusionRules,
            $giataIgs
        );

        $hotels = [];
        foreach ($hotelGenerator as $hotel) {
            $hotels[] = $hotel;
        }

        if (count($filters['occupancy']) > 1) {
            $hotelService = new HotelCombinationService(SupplierNameEnum::HOTEL_TRADER->value);
            $clientResponse[$supplierName] = $hotelService->enrichmentRoomCombinations($hotels, $filters);
        } else {
            $clientResponse[$supplierName] = $hotels;
        }

        $bookingItems[$supplierName] = $this->hotelTraderHotelPricingTransformer->bookingItems ?? [];

        Log::info('HotelApiHandler | availability | DTO hotelTraderResponse '.(microtime(true) - $st).'s');

        $countResponse += count($dataResponse[$supplierName]);
        $totalPages[$supplierName] = $hotelTraderResponse['total_pages'] ?? 0;
        $countClientResponse += count($clientResponse[$supplierName]);

        return [
            'error' => Arr::get($supplierResponse, 'error'),
            'dataResponse' => $dataResponse,
            'clientResponse' => $clientResponse,
            'countResponse' => $countResponse,
            'totalPages' => $totalPages,
            'countClientResponse' => $countClientResponse,
            'bookingItems' => $bookingItems ?? [],
            'dataOriginal' => $dataOriginal ?? [],
        ];
    }

    /**
     * Save booking info to metadata.
     */
    private function saveBookingInfo(array $filters, array $bookingData, array $mainGuest): void
    {
        $filters['supplier_id'] = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;

        $reservation['bookingId'] = Arr::get($bookingData, 'response.htConfirmationCode');
        $reservation['main_guest'] = Arr::get($mainGuest, '0.lastName', '');

        SaveBookingMetadata::dispatch($filters, $reservation);
    }
}
