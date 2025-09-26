<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveBookingItems;
use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveReservations;
use App\Jobs\SaveSearchInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use App\Models\Property;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use App\Repositories\ApiSearchInspectorRepository;
use App\Repositories\ChannelRepository;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\API\Suppliers\ExpediaSupplier\PropertyPriceCall;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelBookingRetrieveBookingTransformer;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelBookTransformer;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelPricingTransformer;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;

class ExpediaBookApiController extends BaseBookApiController
{
    private const PAYMENTS_TYPE = 'affiliate_collect';

    private array $base_params;

    public function __construct(
        private readonly RapidClient $rapidClient,
        private readonly ExpediaHotelBookTransformer $expediaBookDto,
        private readonly ExpediaHotelPricingTransformer $ExpediaHotelPricingDto,
        private readonly PricingRulesTools $pricingRulesService,
        private readonly ExpediaHotelBookingApiController $expediaHotelBookingApiController,
    ) {
        $this->base_params = config('booking-suppliers.Expedia.supplier_expedia_rate_type') === 'package'
            ? PropertyPriceCall::PACKAGE_RATES : PropertyPriceCall::STANDALONE_RATES;
    }

    public function availabilityChange(array $filters, $type = 'change'): ?array
    {
        $booking_item = $filters['booking_item'];
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();
        $search_id = $bookingItem->search_id;

        foreach ($filters['occupancy'] as $room) {
            if (isset($room['children_ages'])) {
                $params['occupancy'][] = $room['adults'].'-'.implode(',', $room['children_ages']);
            } else {
                $params['occupancy'][] = $room['adults'];
            }
        }

        $linkAvailability = ApiSearchInspectorRepository::getLinkAvailability($search_id, $bookingItem);
        if (! $linkAvailability) {
            return ['error' => 'This item is not available for modification.'];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $change_search_id = (string) Str::uuid();
        $searchInspector = ApiSearchInspectorRepository::newSearchInspector(
            [$change_search_id, $filters, [$supplierId], $type, 'hotel']
        );

        $params['checkin'] = $filters['checkin'];
        $params['checkout'] = $filters['checkout'];

        $headers = $this->headers();

        $booking_item_data = json_decode($bookingItem->booking_item_data, true);

        // TODO: test get data from v3/properties/availability
        if (Arr::get($filters, 'availability')) {
            $url = 'v3/properties/availability';
            $params = array_merge($params, $this->base_params);
            unset($params['token']);
            $params['property_id'] = $booking_item_data['hotel_supplier_id'];
            $params['currency'] = $filters['currency'] ?? 'USD';
            $params['country_code'] = $filters['country_code'] ?? 'US';
            $params['language'] = 'en-US';
            $params['rate_plan_count'] = PropertyPriceCall::RATE_PLAN_COUNT;
        } else {
            // Booking Get query Availability
            $props = $this->getPathParamsFromLink($linkAvailability);
            $params['token'] = $props['paramToken']['token'];
            $url = $props['path'];
            // TODO: It doesn't work for 'Test'.
        }

        $originalRQ = [
            'params' => $params,
            'path' => $url,
            'headers' => $this->headers(),
        ];

        unset($headers['Test']);

        try {
            $response = $this->rapidClient->get($url, $params, $headers);

            $originalResponse = json_decode($response->getBody()->getContents(), true) ?? [];

            $dataResponse = $originalResponse;
            $dataResponse['original']['response'] = $originalResponse;
            $dataResponse['original']['request'] = $originalRQ;
        } catch (ClientException $e) {
            return $this->handleExceptionSearchInspector($e, $searchInspector, $originalRQ, 'Expedia Client error', 'Client error');
        } catch (ConnectException $e) {
            return $this->handleExceptionSearchInspector($e, $searchInspector, $originalRQ, 'Expedia Connection timeout', 'Connection timeout');
        } catch (ServerException $e) {
            return $this->handleExceptionSearchInspector($e, $searchInspector, $originalRQ, 'Expedia Server error', 'Server error');
        } catch (RequestException $e) {
            return $this->handleExceptionSearchInspector($e, $searchInspector, $originalRQ, 'Expedia Request Exception occurred', 'Request Exception occurred');
        } catch (Exception $e) {
            return $this->handleExceptionSearchInspector($e, $searchInspector, $originalRQ, 'Expedia Unexpected error', 'Unexpected error');
        }

        if (empty($dataResponse)) {
            return [
                'error' => 'Booking not changed.',
                'booking_item' => $filters['booking_item'],
            ];
        }

        $giata_id = Arr::get($booking_item_data, 'hotel_id');
        $hotel_giata_name = Property::where('code', $giata_id)->first()->name;

        $output = [];
        // add price to response
        $output[$giata_id] = ['giata_id' => $giata_id, 'hotel_name' => $hotel_giata_name] + $originalResponse[0];

        $pricingRules = $this->pricingRulesService->rules($filters);

        $dtoData = $this->ExpediaHotelPricingDto->ExpediaToHotelResponse($output, $filters, $search_id, $pricingRules);

        $bookingItems = $dtoData['bookingItems'];
        $clientResponse = $dtoData['response'];
        $clientResponse['change_search_id'] = $change_search_id;

        /** Save data to Inspector */
        Log::info('HotelApiHandler | price | SaveSearchInspector | start');
        SaveSearchInspector::dispatch($searchInspector, $dataResponse['original'], $originalResponse, $clientResponse);

        /** Save booking_items */
        if (! empty($bookingItems)) {
            foreach ($bookingItems as $item) {
                SaveBookingItems::dispatch($item);
            }
        }

        $filters['hotel_id'] = Arr::get($booking_item_data, 'hotel_id');
        $filters['room_id'] = Arr::get($booking_item_data, 'room_id');
        $filters['rate'] = Arr::get($booking_item_data, 'rate');
        $filters['bed_groups'] = Arr::get($booking_item_data, 'bed_groups');
        $filters['search_id'] = $search_id;
        $filters['change_search_id'] = $change_search_id;

        $this->expediaHotelBookingApiController->addItem($filters, 'change', ['Test' => 'hard_change']);

        return [
            'result' => array_values($clientResponse),
            'change_search_id' => $change_search_id,
        ];
    }

    public function changeSoftBooking(array $filters): ?array
    {
        // step 1 Get room_id from ApiBookingItem
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $room_id = json_decode($bookingItem->booking_item_data, true)['room_id'];

        // step 2 Read Booking Inspector, Get link  PUT method from 'add_item | get_book'
        $linkPutMethod = BookingRepository::getLinkPutMethod($filters['booking_id'], $filters['booking_item'], $room_id);
        if (! $linkPutMethod) {
            return ['error' => 'This item is not available for modification.', 'booking_item' => $filters['booking_item']];
        }

        $search_id = BookingRepository::getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];
        $dataResponse = [];

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'change_book', 'change-soft', 'hotel',
        ]);

        $given_name = Arr::get($filters, 'passengers.0.given_name', null);
        $family_name = Arr::get($filters, 'passengers.0.family_name', null);
        $special_request = Arr::get($filters, 'special_requests.0.special_request', null);
        if ($given_name) {
            $bodyArr['given_name'] = $given_name;
        }
        if ($family_name) {
            $bodyArr['family_name'] = $family_name;
        }
        if ($special_request) {
            $bodyArr['special_request'] = $special_request;
        }

        if (empty($bodyArr)) {
            return ['error' => 'No data to change.'];
        }

        // Booking PUT query
        $props = $this->getPathParamsFromLink($linkPutMethod);
        $body = json_encode($bodyArr);

        $originalRQ = [
            'params' => $props['paramToken'],
            'path' => $props['path'],
            'headers' => $this->headers(),
            'body' => json_decode($body, true),
        ];

        try {
            $response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents(), true) ?? [];
            $headersResponse = $response->getHeaders() ?? [];

            $dataResponse['original']['response'] = array_merge($dataResponse, ['headers' => $headersResponse]);
            $dataResponse['original']['request'] = $originalRQ;
        } catch (ClientException $e) {
            $this->handleException($e, $bookingInspector, 'Client error', $e->getMessage(), $originalRQ);
        } catch (ConnectException $e) {
            $this->handleException($e, $bookingInspector, 'Connection timeout', 'Connection timeout', $originalRQ);
        } catch (ServerException $e) {
            $this->handleException($e, $bookingInspector, 'Server error', 'Server error', $originalRQ);
        } catch (RequestException $e) {
            $this->handleException($e, $bookingInspector, 'Request Exception occurred', $e->getMessage(), $originalRQ);
        } catch (Exception $e) {
            $this->handleException($e, $bookingInspector, 'Unexpected error', $e->getMessage(), []);
            $this->handleException($e, $bookingInspector, 'Unexpected error', $e->getMessage(), $originalRQ);
        }

        if (empty($dataResponse)) {
            $err = $e ? $e->getMessage() : '';

            return ['error' => 'Booking not changed. '.$err, 'booking_item' => $filters['booking_item']];
        }

        SaveBookingInspector::dispatch($bookingInspector, $dataResponse, $dataResponse);

        // run retrieveBooking to get the updated booking
        $item = ApiBookingsMetadataRepository::bookedItem($filters['booking_id'], $filters['booking_item'])->first();
        $this->retrieveBooking($filters, $item);

        return ['status' => 'Booking changed.'];
    }

    public function priceCheck(array $filters): ?array
    {
        return ['result' => 'Not implemented yet.'];
    }

    /**
     * @throws Exception
     */
    public function book(array $filters, ApiBookingInspector $bookingInspector): ?array
    {
        $booking_id = $bookingInspector->booking_id;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;
        $error = [
            'error' => [],
            'supplier_error' => false,
        ];

        Log::info("BOOK ACTION - EXPEDIA - $booking_id", ['filters' => $filters]); // $booking_id

        $passengers = BookingRepository::getPassengers($booking_id, $filters['booking_item']);

        if (! $passengers) {
            Log::info("BOOK ACTION - ERROR - EXPEDIA - $booking_id", ['error' => 'Passengers not found.', 'filters' => $filters]); // $booking_id

            return [
                'error' => 'Passengers not found.',
                'booking_item' => $filters['booking_item'],
            ];
        } else {
            $passengersArr = $passengers->toArray();
            $dataPassengers = json_decode($passengersArr['request'], true);
        }

        $queryHold = $filters['query']['hold'] ?? false;

        $dataResponse = json_decode(Storage::get($bookingInspector->response_path));

        /*
         * With this condition we validate the booking item. It has to have the available status and have the book property
         * Some bookings has the 'sold_out' status with no book link and this is causing an "undefined property: stdClass::$book" exception
         */
        if ($dataResponse->status !== 'available' || ! property_exists($dataResponse->links, 'book')) {
            return [
                'error' => ['The room you are trying to book is not available, please try again with another room'],
            ];
        }

        $linkBookItineraries = $dataResponse->links->book->href;
        $props = $this->getPathParamsFromLink($linkBookItineraries);

        $bodyArr['email'] = $filters['booking_contact']['email'];
        $bodyArr['phone'] = $filters['booking_contact']['phone'];
        $filters['booking_contact']['given_name'] = $filters['booking_contact']['first_name'];
        $filters['booking_contact']['family_name'] = $filters['booking_contact']['last_name'];
        unset($filters['booking_contact']['first_name'], $filters['booking_contact']['last_name']);

        $bodyArr['rooms'] = [];
        foreach ($dataPassengers['rooms'] as $room) {
            $bodyArr['rooms'][] = $room[0];
        }
        $bodyArr['payments'][]['billing_contact'] = $filters['booking_contact'];

        $bodyArr['affiliate_reference_id'] = 'Fora_'.time();

        foreach ($bodyArr['payments'] as $key => $payment) {
            $bodyArr['payments'][$key]['type'] = self::PAYMENTS_TYPE;
        }

        $special_requests = $filters['special_requests'] ?? [];
        foreach ($special_requests as $special_request) {
            if ($special_request['booking_item'] == $filters['booking_item'] &&
                isset($bodyArr['rooms'][$special_request['room'] - 1])) {
                $bodyArr['rooms'][$special_request['room'] - 1]['special_requests'] = $special_request['special_request'];
            }
        }

        $body = json_encode($bodyArr);
        $content = [];
        $originalRQ = [
            'params' => $props['paramToken'],
            'path' => $props['path'],
            'headers' => $this->headers(),
            'body' => json_decode($body, true),
        ];

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $inspectorBook = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'create'.($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

        try {
            Log::info("BOOK ACTION - REQUEST TO EXPEDIA START - EXPEDIA - $booking_id", ['filters' => $filters]); // $booking_id
            $sts = microtime(true);
            $response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $this->headers());
            Log::info("BOOK ACTION - REQUEST TO EXPEDIA FINISH - EXPEDIA - $booking_id", ['time' => (microtime(true) - $sts).' seconds', 'filters' => $filters]); // $booking_id

            $content = json_decode($response->getBody()->getContents(), true);

            $content['original']['response'] = $content;
            $content['original']['request'] = $originalRQ;

            $confirmationNumbers = [
                'confirmation_number' => $content['itinerary_id'] ?? '',
                'type' => SupplierNameEnum::EXPEDIA->value,
            ];

            $clientResponse = $this->expediaBookDto->toHotelBookResponseModel($filters, $confirmationNumbers);
            SaveBookingInspector::dispatchSync($inspectorBook, $content, $clientResponse);

        } catch (ConnectException $e) {
            Log::info("BOOK ACTION - ERROR - EXPEDIA - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id

            $this->handleException($e, $inspectorBook, 'Connection timeout', 'Connection timeout', $originalRQ);
        } catch (ServerException $e) {
            Log::info("BOOK ACTION - ERROR - EXPEDIA - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id

            $this->handleException($e, $inspectorBook, 'Server error', 'Server error', $originalRQ);
        } catch (RequestException $e) {
            Log::info("BOOK ACTION - ERROR - EXPEDIA - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id

            $this->handleException($e, $inspectorBook, 'Request Exception occurred', $e->getMessage(), $originalRQ);

            return [
                'error' => [...$error['error'], $e->getMessage()],
                'supplier_error' => true,
            ];
        } catch (Exception $e) {
            Log::info("BOOK ACTION - ERROR - EXPEDIA - $booking_id", ['error' => $e->getMessage(), 'filters' => $filters, 'trace' => $e->getTraceAsString()]); // $booking_id

            $this->handleException($e, $inspectorBook, 'Unexpected error', $e->getMessage(), $originalRQ);
        }

        if (empty($content)) {
            Log::info("BOOK ACTION - ERROR - EXPEDIA - $booking_id", ['error' => 'Empty content', 'filters' => $filters]); // $booking_id

            return [];
        }

        // Save Book data to Reservation
        SaveReservations::dispatchSync($booking_id, $filters, $dataPassengers, request()->bearerToken());

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = $dataResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        $this->saveBookingInfo($filters, $content, $bookingInspector, true);

        // run retrieveBooking to get the updated booking
        $item = ApiBookingsMetadataRepository::bookedItem($booking_id, $filters['booking_item'])->first();
        $this->retrieveBooking($filters, $item, true);

        // after retrieveBooking, we need to update the ApiBookingsMetadata with the cancellation_paths
        $this->saveBookingInfo($filters, $content, $bookingInspector);

        $error = empty($error['error']) ? [] : $error;

        return [
            ...$res,
            ...$error,
        ];
    }

    public function listBookings(): ?array
    {
        $token_id = ChannelRepository::getTokenId(request()->bearerToken());

        $filters['api_client']['id'] = data_get(request()->all(), 'api_client.id');
        $filters['api_client']['email'] = data_get(request()->all(), 'api_client.email');

        // step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $list = BookingRepository::getAffiliateReferenceIdByChannel($token_id, $filters);
        $path = '/v3/itineraries';

        $promises = [];
        foreach ($list as $item) {
            try {
                $promises[] = $this->rapidClient->getAsync($path, $item, $this->headers());
            } catch (Exception $e) {
                Log::error('Error while creating promise: '.$e->getMessage());
                Log::error($e->getTraceAsString());
            }
        }
        $responses = [];
        $resolvedResponses = Promise\Utils::settle($promises)->wait();
        foreach ($resolvedResponses as $response) {
            if ($response['state'] === 'fulfilled') {
                $data = $response['value']->getBody()->getContents();
                if (! empty(json_decode($data, true))) {
                    $responses[] = json_decode($data, true);
                }
            } else {
                Log::error('ExpediaBookApiHandler | listBookings  failed: '.$response['reason']->getMessage());
                Log::error($e->getTraceAsString());
            }
        }

        return $responses;
    }

    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, bool $isSync = false): ?array
    {
        $booking_id = $filters['booking_id'];
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;
        $filters['search_id'] = ApiBookingItemRepository::getSearchId($filters['booking_item']);

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'retrieve', $apiBookingsMetadata->search_type,
        ]);

        $props = $this->getPathParamsFromLink($apiBookingsMetadata->booking_item_data['retrieve_path']);
        $originalRQ = [
            'params' => $props['paramToken'],
            'path' => $props['path'],
            'headers' => $this->headers(),
        ];

        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents(), true);
            $dataResponse['original']['response'] = $dataResponse;
            $dataResponse['original']['request'] = $originalRQ;
        } catch (ConnectException $e) {
            $this->handleException($e, $bookingInspector, 'Connection timeout', 'Connection timeout', $originalRQ);

            return ['error' => $e->getMessage()];
        } catch (ServerException $e) {
            $this->handleException($e, $bookingInspector, 'Server error', 'Server error', $originalRQ);

            return ['error' => $e->getMessage()];
        } catch (RequestException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            $errorData = json_decode($responseBody, true);
            $errorMessage = $errorData['message'] ?? $e->getMessage();
            $this->handleException($e, $bookingInspector, 'Request Exception occurred', $errorMessage, $originalRQ);

            return ['error' => $errorMessage];
        } catch (\Throwable $e) {
            $this->handleException($e, $bookingInspector, 'Unexpected error', $e->getMessage(), $originalRQ);

            return ['error' => $e->getMessage()];
        }

        $clientDataResponse = ExpediaHotelBookingRetrieveBookingTransformer::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse['original']['response']);

        if ($isSync) {
            SaveBookingInspector::dispatchSync($bookingInspector, $dataResponse, $clientDataResponse);
        } else {
            SaveBookingInspector::dispatch($bookingInspector, $dataResponse, $clientDataResponse);
        }

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return (array) $dataResponse;
        } else {
            return $clientDataResponse;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): ?array
    {
        $booking_id = $filters['booking_id'];
        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $inspectorCancel = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'cancel_booking', 'true', 'hotel',
        ]);

        $room = $apiBookingsMetadata->supplier_booking_item_id;

        $linkDeleteItem = Arr::get($apiBookingsMetadata->booking_item_data, 'cancellation_paths.0', null);

        if (! $linkDeleteItem) {
            $apiBookingItem = ApiBookingItem::where('booking_item', $apiBookingsMetadata->booking_item)->first();
            $room_id = json_decode($apiBookingItem->booking_item_data, true)['room_id'];
            $linkDeleteItem = BookingRepository::getLinkDeleteItem($filters['booking_id'], $apiBookingsMetadata->booking_item, $room_id)[0];
        }

        // Delete item DELETE method query
        $props = $this->getPathParamsFromLink($linkDeleteItem);
        $path = $props['path'];
        $itineraryId = Arr::get(explode('/', $path), '3', BookingRepository::getItineraryId($filters, $supplierId));

        $bodyArr = [
            'itinerary_id' => $itineraryId,
            'room_id' => $room,
        ];
        $body = json_encode($bodyArr);
        $originalRQ = [
            'params' => $props['paramToken'],
            'path' => $props['path'],
            'headers' => $this->headers(),
            'body' => json_decode($body, true),
        ];

        try {
            $response = $this->rapidClient->delete($path, $props['paramToken'], $body, $this->headers());
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'room' => $room,
                'status' => 'Room canceled.',
            ];
            $dataResponse = json_decode($response->getBody()->getContents(), true) ?? $res;

            $dataResponse['original']['response'] = $dataResponse;
            $dataResponse['original']['request'] = $originalRQ;

            SaveBookingInspector::dispatch($inspectorCancel, $dataResponse, $res);
        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $responseErrorArr = json_decode($responseError[1], true);
            $message = $responseErrorArr['message'];
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'room' => $room,
                'status' => $message,
            ];
            SaveBookingInspector::dispatch($inspectorCancel, $responseError, $res, 'error',
                ['side' => 'app', 'message' => $message]);
        }

        return $res;
    }

    private function handleException(Exception $e, $bookingInspector, $logMessage, $errorMessage, $originalRQ): void
    {
        Log::error($logMessage.': '.$e->getMessage());
        Log::error($e->getTraceAsString());
        $content = [];
        if ($originalRQ !== null) {
            $content['original']['request'] = $originalRQ;
        }
        SaveBookingInspector::dispatch($bookingInspector, $content, [], 'error', ['side' => 'supplier', 'message' => $errorMessage]);
    }

    private function handleExceptionSearchInspector(Exception $e, $searchInspector, $originalRQ, $errorMessage, $logMessage)
    {
        Log::error($logMessage.': '.$e->getMessage());
        Log::error($e->getTraceAsString());
        SaveSearchInspector::dispatch($searchInspector, ['request' => $originalRQ], [], [], 'error',
            ['side' => 'supplier', 'message' => $errorMessage, 'parent_search_id' => $searchInspector['search_id']]);
        Log::error($logMessage.': '.$e->getMessage());

        return ['error' => $errorMessage];
    }

    private function getPathParamsFromLink(string $link): array
    {
        $arr_link = explode('?', $link);
        $path = $arr_link[0];
        $arr_param = explode('=', $arr_link[1]);
        $paramToken = [$arr_param[0] => str_replace('token=', '', $arr_link[1])];

        return ['path' => $path, 'paramToken' => $paramToken];
    }

    /**
     * @return string[]
     */
    private function headers(): array
    {
        return [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function saveBookingInfo(array $filters, array $content, ApiBookingInspector $bookingInspector, bool $isSync = false): void
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $roomId = json_decode($bookingInspector->request)?->room_id;
        $itinerary_id = Arr::get($content, 'itinerary_id');

        $filters['supplier_id'] = $supplierId;
        $linkBookRetrieves = Arr::get($content, 'links.retrieve.href');

        $reservation = [
            'bookingId' => $itinerary_id,
            'cancellation_paths' => BookingRepository::getLinkDeleteItem($filters['booking_id'], $filters['booking_item'], $roomId),
            'retrieve_path' => $linkBookRetrieves,
        ];

        if ($isSync) {
            SaveBookingMetadata::dispatchSync($filters, $reservation);
        } else {
            SaveBookingMetadata::dispatch($filters, $reservation);
        }
    }
}
