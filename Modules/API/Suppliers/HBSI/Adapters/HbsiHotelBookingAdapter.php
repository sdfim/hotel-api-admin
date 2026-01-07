<?php

namespace Modules\API\Suppliers\HBSI\Adapters;

use App\Jobs\MoveBookingItemCache;
use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use App\Repositories\ChannelRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\Base\Adapters\BaseHotelBookingAdapter;
use Modules\API\Suppliers\Base\Traits\HotelBookingavAilabilityChangeTrait;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierInterface;
use Modules\API\Suppliers\HBSI\Client\HbsiClient;
use Modules\API\Suppliers\HBSI\Transformers\HbsiHotelBookingRetrieveBookingTransformer;
use Modules\API\Suppliers\HBSI\Transformers\HbsiHotelBookTransformer;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;
use SimpleXMLElement;

class HbsiHotelBookingAdapter extends BaseHotelBookingAdapter implements HotelBookingSupplierInterface
{
    use HotelBookingavAilabilityChangeTrait;

    private const CONFIRMATION = [
        '8' => 'HBSI',
        '10' => 'Synxis',
        '14' => 'Own',
        '3' => 'UltimateJet',
    ];

    private const ALREADY_CANCELLED_CODE = '95';

    private const NON_CANCELLABLE_BOOKING_CODE_ERRORS = [
        '394', // Cancelled after due date
        '450', // Reservation Expired
        '97', // Not Found
    ];

    private const CODE_WRONG_PASSENGER_NAME = '251';

    private const MAX_CANCEL_BOOKING_RETRY_COUNT = 1;

    public function __construct(
        private readonly HbsiClient $hbsiClient,
        private readonly HbsiHotelAdapter $hotelAdapter,
        private readonly HbsiHotelBookTransformer $hbsiHotelBookDto,
        private readonly PricingRulesTools $pricingRulesService,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::HBSI;
    }

    /**
     * @throws GuzzleException
     */
    public function book(array $filters, ApiBookingInspector $bookingInspector): ?array
    {
        $booking_id = $bookingInspector->booking_id;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;

        Log::info("BOOK ACTION - HBSI - $booking_id", ['filters' => $filters]); // $booking_id

        $passengers = ApiBookingInspectorRepository::getPassengers($booking_id, $filters['booking_item']);

        if (! $passengers) {
            Log::info("BOOK ACTION - ERROR - HBSI - $booking_id", ['error' => 'Passengers not found', 'filters' => $filters]); // $booking_id

            return [
                'error' => 'Passengers not found.',
                'booking_item' => $filters['booking_item'],
            ];
        } else {
            $passengersArr = $passengers->toArray();
            $dataPassengers = json_decode($passengersArr['request'], true);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $inspectorBook = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id,
            $filters,
            $supplierId,
            'book',
            'create',
            $bookingInspector->search_type,
        ]);

        $error = true;
        try {
            Log::info('HbsiBookApiController | book | '.json_encode($filters));
            Log::info("BOOK ACTION - REQUEST TO HBSI START - HBSI - $booking_id", ['filters' => $filters]); // $booking_id
            $sts = microtime(true);
            $xmlPriceData = $this->hbsiClient->handleBook($filters, $inspectorBook);
            Log::info("BOOK ACTION - REQUEST TO HBSI FINISH - HBSI - $booking_id", ['time' => (microtime(true) - $sts).' seconds', 'filters' => $filters]); // $booking_id

            if (isset($xmlPriceData['error'])) {
                Log::info("BOOK ACTION - ERROR - HBSI - $booking_id", ['error' => $xmlPriceData['error'], 'filters' => $filters]); // $booking_id

                return [
                    'error' => $xmlPriceData['error'],
                    'booking_item' => $filters['booking_item'] ?? '',
                    'supplier' => SupplierNameEnum::HBSI->value,
                    'supplier_error' => true,
                ];
            }

            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true) ?? [];

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
                'main_guest' => $xmlPriceData['main_guest'],
            ];
            if (! isset($dataResponse['Errors'])) {
                // Save Booking Info
                $this->saveBookingInfo($filters, $dataResponse, json_decode($xmlPriceData['main_guest'], true));

                $inputConfirmationNumbers = $dataResponse['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'] ?? [];
                $confirmationNumbers = array_map(function ($item) {
                    return [
                        'confirmation_number' => $item['@attributes']['ResID_Value'],
                        'type' => self::CONFIRMATION[$item['@attributes']['ResID_Type']] ?? $item['@attributes']['ResID_Type'],
                    ];
                }, $inputConfirmationNumbers);
                $clientResponse = $this->hbsiHotelBookDto->toHotelBookResponseModel($filters, $confirmationNumbers);

                $error = false;
            } else {
                $clientResponse = $dataResponse['Errors'];
                $clientResponse['booking_item'] = $filters['booking_item'];
                $clientResponse['supplier'] = SupplierNameEnum::HBSI->value;
            }

        } catch (RequestException $e) {
            Log::info("BOOK ACTION - ERROR - HBSI - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id
            Log::error('HbsiBookApiController | book | RequestException '.$e->getResponse()->getBody());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch(
                $inspectorBook,
                [],
                [],
                'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]
            );

            return [
                'error' => 'Request Error. '.$e->getResponse()->getBody(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HBSI->value,
            ];
        } catch (\Exception $e) {
            Log::info("BOOK ACTION - ERROR - HBSI - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id
            Log::error('HbsiBookApiController | book | Exception '.$e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch(
                $inspectorBook,
                [],
                [],
                'error',
                ['side' => 'app', 'message' => $e->getMessage()]
            );

            return [
                'error' => 'Unexpected Error. '.$e->getMessage(),
                'booking_item' => $filters['booking_item'] ?? '',
                'supplier' => SupplierNameEnum::HBSI->value,
            ];
        }

        if (! $error) {
            SaveBookingInspector::dispatch($inspectorBook, $dataResponseToSave, $clientResponse);
            // Save Book data to Reservation
            SaveReservations::dispatch($booking_id, $filters, $dataPassengers, request()->bearerToken());
        }

        if (! $dataResponse) {
            Log::info("BOOK ACTION - ERROR - HBSI - $booking_id", ['error' => 'Empty dataResponse', 'filters' => $filters]); // $booking_id

            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = (array) $dataResponse;
        } elseif ($error) {
            $res = $clientResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        return $res;
    }

    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, SupplierNameEnum $supplier, bool $isSync = false): ?array
    {
        $booking_id = $filters['booking_id'];
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;
        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id,
            $filters,
            $supplierId,
            'book',
            'retrieve',
            $apiBookingsMetadata->search_type,
        ]);

        $changePassengersInspector = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $apiBookingsMetadata->booking_item)
            ->where('type', 'change_passengers')->first();
        if ($changePassengersInspector) {
            $rooms = json_decode($changePassengersInspector->request, true)['rooms'];
            $reservation = [
                'booking_id' => $apiBookingsMetadata->supplier_booking_item_id,
                'name' => $rooms[0][0]['given_name'],
                'surname' => $rooms[0][0]['family_name'],
            ];
        } else {
            $bookingItemData = $apiBookingsMetadata->booking_item_data;
            $name = Arr::get($bookingItemData, 'main_guest.GivenName') ?? Arr::get($bookingItemData, 'main_guest.0.GivenName');
            $surname = Arr::get($bookingItemData, 'main_guest.Surname') ?? Arr::get($bookingItemData, 'main_guest.0.Surname');

            $reservation = [
                'booking_id' => $apiBookingsMetadata->supplier_booking_item_id,
                'name' => $name,
                'surname' => $surname,
            ];
        }

        $xmlPriceData = $this->hbsiClient->retrieveBooking(
            $reservation,
            $apiBookingsMetadata->hotel_supplier_id ?? null,
            $bookingInspector
        );

        if (! $xmlPriceData['response'] instanceof SimpleXMLElement) {
            return [];
        }
        $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();

        $dataResponse = json_decode(json_encode($response), true);

        if (isset($dataResponse['Errors'])) {
            return [];
        }

        $dataResponseToSave = $dataResponse;
        $dataResponseToSave['original'] = [
            'request' => $xmlPriceData['request'],
            'response' => $xmlPriceData['response']->asXML(),
        ];

        $clientDataResponse = $dataResponse['Errors'] ?? HbsiHotelBookingRetrieveBookingTransformer::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse);

        SaveBookingInspector::dispatch($bookingInspector, $dataResponseToSave, $clientDataResponse);

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return (array) $dataResponse;
        } else {
            return $clientDataResponse;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, int $iterations = 0): ?array
    {
        $booking_id = $filters['booking_id'];

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $inspectorCansel = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id,
            $filters,
            $supplierId,
            'cancel_booking',
            'true',
            'hotel',
        ]);

        try {
            $xmlPriceData = $this->hbsiClient->cancelBooking(
                $apiBookingsMetadata->booking_item_data,
                $apiBookingsMetadata->hotel_supplier_id ?? null,
                $inspectorCansel
            );
            $response = $xmlPriceData['response']->children('soap-env', true)->Body->children()->children();
            $dataResponse = json_decode(json_encode($response), true);

            $dataResponseToSave = $dataResponse;
            $dataResponseToSave['original'] = [
                'request' => $xmlPriceData['request'],
                'response' => $xmlPriceData['response']->asXML(),
            ];

            if (isset($dataResponse['Errors'])) {
                $res = $dataResponse['Errors'];
                $code = $response->children()->attributes()['Code'];

                if (static::ALREADY_CANCELLED_CODE == $code) {
                    return [
                        'booking_item' => $apiBookingsMetadata->booking_item,
                        'status' => 'Room canceled.',
                    ];
                }

                if (in_array($code, static::NON_CANCELLABLE_BOOKING_CODE_ERRORS)) {
                    return [
                        ...$res,
                        'booking_item' => $apiBookingsMetadata->booking_item,
                        'cancellable' => false,
                    ];
                }

                if (static::CODE_WRONG_PASSENGER_NAME == $code) {
                    $mainGuest = $this->getNameFromError(Arr::get($dataResponse, 'Errors.Error'));

                    if ($mainGuest === null) {
                        return $dataResponse['Errors'];
                    }

                    $data = [
                        ...$apiBookingsMetadata->booking_item_data,
                        'main_guest' => $mainGuest,
                    ];

                    $apiBookingsMetadata = ApiBookingsMetadataRepository::updateBookingItemData($apiBookingsMetadata, $data);

                    if ($iterations < static::MAX_CANCEL_BOOKING_RETRY_COUNT) {
                        return $this->cancelBooking($filters, $apiBookingsMetadata, $iterations + 1);
                    }
                }
            } else {
                $res = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'status' => 'Room canceled.',
                ];

                SaveBookingInspector::dispatch($inspectorCansel, $dataResponseToSave, $res);
            }
        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());

            $message = isset($responseError[1])
                ? json_decode($responseError[1], true)['message']
                : $e->getMessage();
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'status' => $message,
                'Error' => $message,
            ];

            $dataResponseToSave = is_array($message) ? $message : [];

            SaveBookingInspector::dispatch(
                $inspectorCansel,
                $dataResponseToSave,
                $res,
                'error',
                ['side' => 'app', 'message' => $message]
            );
        }

        return $res;
    }

    /**
     * @throws GuzzleException
     */
    public function listBookings(): ?array
    {
        $tokenId = ChannelRepository::getTokenId(request()->bearerToken());
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->value('id');

        $apiClientId = data_get(request()->all(), 'api_client.id');
        $apiClientEmail = data_get(request()->all(), 'api_client.email');

        $itemsBooked = ApiBookingInspector::query()
            ->where('token_id', $tokenId)
            ->where('supplier_id', $supplierId)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->when(filled($apiClientId), fn ($q) => $q->whereJsonContains('request->api_client->id', (int) $apiClientId))
            ->when(filled($apiClientEmail), fn ($q) => $q->whereJsonContains('request->api_client->email', (string) $apiClientEmail))
            ->has('metadata')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $data = [];
        foreach ($itemsBooked as $item) {
            $filters['booking_id'] = $item->metadata?->booking_id;
            $data[] = $this->retrieveBooking($filters, $item->metadata, SupplierNameEnum::HBSI);
        }

        return $data;
    }

    public function changeBooking(array $filters, string $mode = 'soft'): ?array
    {
        $dataResponse = [];
        $soapError = false;

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $filters['booking_id'],
            $filters,
            $supplierId,
            'change_book',
            'change-'.$mode,
            'hotel',
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
                SaveBookingInspector::dispatch(
                    $bookingInspector,
                    $dataResponseToSave,
                    [],
                    'error',
                    ['side' => 'app', 'message' => $xmlPriceData['response']]
                );

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
            Log::error('HbsiBookApiController | changeBooking '.$e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $dataResponse = json_decode(''.$e->getResponse()->getBody());

            SaveBookingInspector::dispatch(
                $bookingInspector,
                $dataResponse,
                [],
                'error',
                ['side' => 'app', 'message' => $e->getResponse()->getBody()]
            );

            return (array) $dataResponse;
        } catch (Exception $e) {
            $dataResponse['Errors'] = [$e->getMessage()];
            Log::error('HbsiBookApiController | changeBooking '.$e->getMessage());
            Log::error(
                'HbsiBookApiController | changeBooking '.$e->getMessage(),
                [
                    'booking_id' => $filters['booking_id'],
                    'dataResponseToSave' => $dataResponseToSave ?? '',
                ]
            );
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch(
                $bookingInspector,
                [],
                [],
                'error',
                ['side' => 'app', 'message' => $e->getMessage()]
            );

            return (array) $dataResponse;
        }

        if (! $dataResponseToSave) {
            return [];
        }

        return ['status' => 'Booking changed.'];
    }

    // TODO: need to be refactored for multiple booking items
    public function priceCheck(array $filters): ?array
    {
        if (isset($filters['new_booking_item']) && Cache::get('room_combinations:'.$filters['new_booking_item'])) {
            $hotelService = new HotelCombinationService(SupplierNameEnum::HBSI->value);
            $hotelService->updateBookingItemsData($filters['new_booking_item'], true);
        } else {
            MoveBookingItemCache::dispatchSync($filters['new_booking_item']);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $filters['booking_id'],
            $filters,
            $supplierId,
            'price-check',
            '',
            'hotel',
        ]);

        $item = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $itemPrice = json_decode($item->booking_pricing_data, true);
        $totalPrice = $itemPrice['total_price'] ?? 0;

        $itemNew = ApiBookingItem::where('booking_item', $filters['new_booking_item'])->first();
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

        SaveBookingInspector::dispatchSync($bookingInspector, [], $data);

        return $data;
    }

    /**
     * This method can receive book $dataResponse or retrieveBooking confirmation_numbers array
     */
    private function extractReservationId(array $dataResponse): array
    {
        $reservation = [];

        if (Arr::has($dataResponse, 'HotelReservations')) {
            $hotelReservationID = $dataResponse['HotelReservations']['HotelReservation']['ResGlobalInfo']['HotelReservationIDs']['HotelReservationID'];

            foreach ($hotelReservationID as $item) {
                $attributes = $item['@attributes'];
                if ($attributes['ResID_Type'] == '8') {
                    $reservation['bookingId'] = $attributes['ResID_Value'];
                } elseif ($attributes['ResID_Type'] == '3') {
                    $reservation['ReservationId'] = $attributes['ResID_Value'];
                }
            }
        } elseif (! empty($dataResponse)) {
            foreach ($dataResponse as $item) {
                if ($item['type_id'] == '8') {
                    $reservation['bookingId'] = $item['confirmation_number'];
                } elseif ($item['type_id'] == '3') {
                    $reservation['ReservationId'] = $item['confirmation_number'];
                }
            }
        }

        return $reservation;
    }

    private function getNameFromError(mixed $error): ?array
    {
        if (empty($error)) {
            return null;
        }

        $pattern = '/GivenName:\s*(\w+),\s*Surname:\s*(\w+)/';

        if (preg_match($pattern, $error, $matches)) {
            return [
                'GivenName' => $matches[1],
                'Surname' => $matches[2],
            ];
        } else {
            return null;
        }
    }

    private function saveBookingInfo(array $filters, array $dataResponse, array $mainGuest): void
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $filters['supplier_id'] = $supplierId;

        $reservation = $this->extractReservationId($dataResponse);
        $reservation['main_guest'] = $mainGuest['PersonName'];

        SaveBookingMetadata::dispatch($filters, $reservation);
    }
}
