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
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use App\Repositories\ApiSearchInspectorRepository;
use App\Repositories\ChannelRepository;
use App\Repositories\HbsiRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\HbsiSupplier\HbsiClient;
use Modules\API\Suppliers\HotelTraderSupplier\HotelTraderClient;
use Modules\API\Suppliers\Transformers\HBSI\HbsiHotelBookTransformer;
use Modules\API\Suppliers\Transformers\HBSI\HbsiHotelPricingTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderHotelBookTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderiHotelBookingRetrieveBookingTransformer;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;
use SimpleXMLElement;

class HotelTraderBookApiController extends BaseBookApiController
{
    public function __construct(
        private readonly HotelTraderClient $hotelTraderClient,
        private readonly HbsiClient $hbsiClient,
        private readonly HbsiHotelBookTransformer $hbsiHotelBookDto,
        private readonly HotelTraderHotelBookTransformer $hotelTraderHotelBookTransformer,
        private readonly HbsiHotelPricingTransformer $HbsiHotelPricingTransformer,
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
            $res = (array) $bookingData;
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

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
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

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
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

    // TODO: Refactor this method to use the new HotelTraderClient
    public function changeBooking(array $filters, string $mode = 'soft'): ?array
    {
        $dataResponse = [];
        $soapError = false;

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $filters['booking_id'], $filters, $supplierId, 'change_book', 'change-'.$mode, 'hotel',
        ]);

        try {
            $xmlPriceData = $this->hbsiClient->modifyBook($filters, $bookingInspector);

            if ($xmlPriceData['response'] instanceof SimpleXMLElement) {
                $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
                $dataResponse = json_decode(json_encode($response), true);
            } else {
                $soapError = true;
            }

            $dataResponseToSave = $dataResponse;
            $mainGuest = Arr::get($xmlPriceData, 'main_guest');
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response'] instanceof SimpleXMLElement ? $xmlPriceData['response']->asXML() : $xmlPriceData['response'],
                'main_guest' => $mainGuest,
            ];
            if ($soapError) {
                SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, [],
                    'error', ['side' => 'app', 'message' => $xmlPriceData['response']]);

                return [$xmlPriceData['response']];
            } elseif (! isset($dataResponse['Errors'])) {
                $clientResponse = $this->hbsiHotelBookDto->toHotelBookResponseModel($filters);
            } else {
                $clientResponse = $dataResponse['Errors'];
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HBSI->value;
            }

            SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientResponse);
            $apiBookingsMetadata = ApiBookingsMetadataRepository::getBookedItem($filters['booking_id'], $filters['booking_item']);
            $data = [
                ...$apiBookingsMetadata->booking_item_data,
                'main_guest' => Arr::get(json_decode($mainGuest, true), 'PersonName', []),
            ];
            ApiBookingsMetadataRepository::updateBookingItemData($apiBookingsMetadata, $data);

        } catch (RequestException $e) {
            Log::error('HotelTraderBookApiController | changeBooking '.$e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $dataResponse = json_decode(''.$e->getResponse()->getBody());

            SaveBookingInspector::dispatch($bookingInspector, $dataResponse, [], 'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]);

            return (array) $dataResponse;
        } catch (Exception $e) {
            $dataResponse['Errors'] = [$e->getMessage()];
            Log::error('HotelTraderBookApiController | changeBooking '.$e->getMessage());
            Log::error('HotelTraderBookApiController | changeBooking '.$e->getMessage(),
                [
                    'booking_id' => $filters['booking_id'],
                    'dataResponseToSave' => $dataResponseToSave ?? '',
                ]);
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($bookingInspector, [], [], 'error',
                ['side' => 'app', 'message' => $e->getMessage()]);

            return (array) $dataResponse;
        }

        if (! $dataResponseToSave) {
            return [];
        }

        return ['status' => 'Booking changed.'];
    }

    // TODO: Refactor this method to use the new HotelTraderClient
    public function availabilityChange(array $filters): ?array
    {
        $booking_item = $filters['booking_item'];
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();
        $searchId = (string) Str::uuid();
        $hotelId = Arr::get(json_decode($bookingItem->booking_item_data, true), 'hotel_id');
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $searchInspector = ApiSearchInspectorRepository::newSearchInspector([$searchId, $filters, [$supplierId], 'change', 'hotel']);

        $response = $this->priceByHotel($hotelId, $filters, $searchInspector);

        // TODO: Check $giataIgs - need to be used in the future from $filters
        $giataIgs = Arr::get($filters, 'giata_ids', []);

        $handleResponse = $this->handlePriceHbsi(
            $response,
            $filters,
            $searchId,
            $this->pricingRulesService->rules($filters, $giataIgs),
            $this->pricingRulesService->rules($filters, $giataIgs, true),
            $giataIgs
        );

        $clientResponse = $handleResponse['clientResponse'];
        $content = ['count' => $handleResponse['countResponse'], 'query' => $filters, 'results' => $handleResponse['dataResponse']];
        $result = [
            'count' => $handleResponse['countClientResponse'],
            'total_pages' => max($handleResponse['totalPages']),
            'query' => $filters,
            'results' => $clientResponse,
        ];

        /** Save data to Inspector */
        SaveSearchInspector::dispatch($searchInspector, $handleResponse['dataOriginal'] ?? [], $content, $result);

        /** Save booking_items */
        if (! empty($handleResponse['bookingItems'])) {
            foreach ($handleResponse['bookingItems'] as $items) {
                SaveBookingItems::dispatch($items);
            }
        }

        return [
            'result' => $clientResponse[SupplierNameEnum::HBSI->value],
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

    // TODO: Refactor this method to use the new HotelTraderClient
    private function priceByHotel(string $hotelId, array $filters, array $searchInspector): ?array
    {
        try {
            $hbsiHotel = HbsiRepository::getByGiataId($hotelId);
            $hotelIds = isset($hbsiHotel['supplier_id']) ? [$hbsiHotel['supplier_id']] : [];

            if (empty($hotelIds)) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            /** get PriceData from HotelTrader */
            $xmlPriceData = $this->hbsiClient->getSyncHbsiPriceByPropertyIds($hotelIds, $filters, $searchInspector);

            if (isset($xmlPriceData['error'])) {
                return [
                    'error' => $xmlPriceData['error'],
                    'original' => [
                        'request' => '',
                        'response' => '',
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $arrayResponse = json_decode(json_encode($response), 1);
            if (isset($arrayResponse['Errors'])) {
                Log::error('HBSIHotelApiHandler | price ', ['supplier response' => $arrayResponse['Errors']['Error']]);
            }
            if (! isset($arrayResponse['RoomStays']['RoomStay'])) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            /**
             * Normally RoomStay is an array when several rates come from the same hotel, if only one rate comes, the
             * array becomes assoc instead of sequential, so we force it to be sequential so the foreach below does not
             * fail
             */
            $priceData = Arr::isAssoc($arrayResponse['RoomStays']['RoomStay'])
                ? [$arrayResponse['RoomStays']['RoomStay']]
                : $arrayResponse['RoomStays']['RoomStay'];

            $i = 1;
            $groupedPriceData = array_reduce($priceData, function ($result, $item) use ($hbsiHotel, &$i) {
                $hotelCode = $item['BasicPropertyInfo']['@attributes']['HotelCode'];
                $roomCode = $item['RoomTypes']['RoomType']['@attributes']['RoomTypeCode'];
                $item['rate_ordinal'] = $i;
                $result[$hotelCode] = [
                    'property_id' => $hotelCode,
                    'hotel_name' => Arr::get($item, 'BasicPropertyInfo.@attributes.HotelName'),
                    'hotel_name_giata' => $hbsiHotel['name'] ?? '',
                    'giata_id' => $hbsiHotel['giata_id'] ?? 0,
                    'rooms' => $result[$hotelCode]['rooms'] ?? [],
                ];
                if (! isset($result[$hotelCode]['rooms'][$roomCode])) {
                    $result[$hotelCode]['rooms'][$roomCode] = [
                        'room_code' => $roomCode,
                        'room_name' => $item['RoomTypes']['RoomType']['RoomDescription']['@attributes']['Name'] ?? '',
                    ];
                }
                $result[$hotelCode]['rooms'][$roomCode]['rates'][] = $item;
                $i++;

                return $result;
            }, []);

            return [
                'original' => [
                    'request' => $xmlPriceData['request'],
                    'response' => $xmlPriceData['response']->asXML(),
                ],
                'array' => $groupedPriceData,
                'total_pages' => 1,
            ];

        } catch (GuzzleException $e) {
            Log::error('HBSIHotelApiHandler GuzzleException '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        } catch (\Throwable $e) {
            Log::error('HBSIHotelApiHandler Exception '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        }
    }

    // TODO: Refactor this method to use the new HotelTraderClient
    private function handlePriceHbsi($supplierResponse, array $filters, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIgs): array
    {
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $countResponse = 0;
        $countClientResponse = 0;

        $hbsiResponse = $supplierResponse;

        $supplierName = SupplierNameEnum::HBSI->name;
        $dataResponse[$supplierName] = $hbsiResponse['array'];
        $dataOriginal[$supplierName] = $hbsiResponse['original'];

        $st = microtime(true);
        $hotelGenerator = $this->HbsiHotelPricingTransformer->HbsiToHotelResponse($hbsiResponse['array'], $filters, $search_id, $pricingRules, $pricingExclusionRules, $giataIgs);

        $clientResponse[$supplierName] = [];
        $count = 0;
        // enrichmentRoomCombinations должен применяться к массиву, поэтому сначала собираем массив
        $hotels = [];
        foreach ($hotelGenerator as $count => $hotel) {
            $hotels[] = $hotel;
        }

        /** Enrichment Room Combinations */
        $countRooms = count($filters['occupancy']);
        if ($countRooms > 1) {
            $hotelService = new HotelCombinationService(SupplierNameEnum::HBSI->value);
            $clientResponse[$supplierName] = $hotelService->enrichmentRoomCombinations($hotels, $filters);
        } else {
            $clientResponse[$supplierName] = $hotels;
        }
        $bookingItems[$supplierName] = $this->HbsiHotelPricingTransformer->bookingItems ?? ($hotelGenerator['bookingItems'] ?? []);

        Log::info('HotelApiHandler | price | DTO hbsiResponse '.(microtime(true) - $st).'s');

        $countResponse += count($hbsiResponse['array']);
        $totalPages[$supplierName] = $hbsiResponse['total_pages'] ?? 0;
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
