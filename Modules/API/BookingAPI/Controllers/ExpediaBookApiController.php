<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveBookingMetadata;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ChannelRenository;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelBookDto;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelBookingRetrieveBookingDto;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Enums\SupplierNameEnum;

class ExpediaBookApiController extends BaseBookApiController
{
    private const PAYMENTS_TYPE = 'affiliate_collect';

    public function __construct(
        private readonly RapidClient $rapidClient = new RapidClient(),
        private readonly ExpediaHotelBookDto $expediaBookDto = new ExpediaHotelBookDto(),
    ) {
    }

    public function changeBooking(array $filters): ?array
    {
        // step 1 Get room_id from ApiBookingItem
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $room_id = json_decode($bookingItem->booking_item_data, true)['room_id'];

        // step 2 Read Booking Inspector, Get link  PUT method from 'add_item | get_book'
        $linkPutMethod = BookingRepository::getLinkPutMethod($filters['booking_id'], $room_id);

        $search_id = BookingRepository::getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];
        $dataResponse = [];

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'change_booking', '', 'hotel',
        ]);

        // Booking PUT query
        $props = $this->getPathParamsFromLink($linkPutMethod);
        $bodyArr = $filters['query'];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (ConnectException $e) {
            $this->handleException($e, $bookingInspector, 'Connection timeout', 'Connection timeout');
        } catch (ServerException $e) {
            $this->handleException($e, $bookingInspector, 'Server error', 'Server error');
        } catch (RequestException $e) {
            $this->handleException($e, $bookingInspector, 'Request Exception occurred', $e->getMessage());
        } catch (Exception $e) {
            $this->handleException($e, $bookingInspector, 'Unexpected error', $e->getMessage());
        }

        if (empty($dataResponse)) {
            return [];
        }

        SaveBookingInspector::dispatch($bookingInspector, $dataResponse, $dataResponse);

        return (array) $dataResponse;
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
            'error'          => [],
            'supplier_error' => false,
        ];

        $passengers = BookingRepository::getPassengers($booking_id, $filters['booking_item']);

        if (! $passengers) {
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

        $bodyArr['affiliate_reference_id'] = 'UJV_'.time();

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

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $inspectorBook = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'create'.($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

        try {
            $response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $this->headers());

            $content = json_decode($response->getBody()->getContents(), true);

            $content['original']['response'] = $content;
            $content['original']['request']['params'] = $props['paramToken'];
            $content['original']['request']['body'] = json_decode($body, true);
            $content['original']['request']['path'] = $props['path'];
            $content['original']['request']['headers'] = $this->headers();

            SaveBookingInspector::dispatch($inspectorBook, array_merge($filters, $bodyArr), $content);
        } catch (ConnectException $e) {
            $this->handleException($e, $inspectorBook, 'Connection timeout', 'Connection timeout');
        } catch (ServerException $e) {
            $this->handleException($e, $inspectorBook, 'Server error', 'Server error');
        } catch (RequestException $e) {
            $this->handleException($e, $inspectorBook, 'Request Exception occurred', $e->getMessage());

            $error = [
                'error'          => [...$error['error'], $e->getMessage()],
                'supplier_error' => true,
            ];
        } catch (Exception $e) {
            $this->handleException($e, $inspectorBook, 'Unexpected error', $e->getMessage());
        }

        if (empty($content)) {
            return [];
        }

        // Save Book data to Reservation
        SaveReservations::dispatch($booking_id, $filters, $dataPassengers);

        $linkBookRetrieves = $content['links']['retrieve']['href'];

        $inspectorBook = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'book', 'retrieve'.($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

        // Booking GET query - Retrieve Booking
        $props = $this->getPathParamsFromLink($linkBookRetrieves);
        $dataResponse = [];

        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents(), true);
            $confirmationNumbers = [
                'confirmation_number' => $dataResponse['itinerary_id'] ?? '',
                'type' => SupplierNameEnum::EXPEDIA->value,
            ];
            SaveBookingInspector::dispatch($inspectorBook, $dataResponse, $dataResponse);
        } catch (ConnectException $e) {
            $this->handleException($e, $inspectorBook, 'Connection timeout', 'Connection timeout');
        } catch (ServerException $e) {
            $this->handleException($e, $inspectorBook, 'Server error', 'Server error');
        } catch (RequestException $e) {
            $this->handleException($e, $inspectorBook, 'Request Exception occurred', $e->getMessage());

            $error = [
                'error'          => [...$error['error'], $e->getMessage()],
                'supplier_error' => true,
            ];
        } catch (Exception $e) {
            $this->handleException($e, $inspectorBook, 'Unexpected error', $e->getMessage());
        }

        $clientResponse = $dataResponse ? $this->expediaBookDto->toHotelBookResponseModel($filters, $confirmationNumbers) : [];

        if (empty($dataResponse)) {
            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = $dataResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        $this->saveBookingInfo($filters, $content, $bookingInspector);

        $error = empty($error['error']) ? [] : $error;

        return [
            ...$res,
            ...$error
        ];
    }

    public function listBookings(): ?array
    {
        $token_id = ChannelRenository::getTokenId(request()->bearerToken());

        // step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $list = BookingRepository::getAffiliateReferenceIdByChannel($token_id);
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
                $responses[] = json_decode($data, true);
            } else {
                Log::error('ExpediaBookApiHandler | listBookings  failed: '.$response['reason']->getMessage());
                Log::error($e->getTraceAsString());
            }
        }

        return $responses;
    }

    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): ?array
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = '';
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'retrieve_booking', '', $apiBookingsMetadata->search_type,
        ]);

        $props = $this->getPathParamsFromLink($apiBookingsMetadata->booking_item_data['retrieve_path']);

        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents(), true);
            $dataResponse['original']['response'] = $dataResponse;
            $dataResponse['original']['request']['params'] = $props['paramToken'];
            $dataResponse['original']['request']['path'] = $props['path'];
            $dataResponse['original']['request']['headers'] = $this->headers();
        } catch (ConnectException $e) {
            $this->handleException($e, $bookingInspector, 'Connection timeout', 'Connection timeout');
        } catch (ServerException $e) {
            $this->handleException($e, $bookingInspector, 'Server error', 'Server error');
        } catch (RequestException $e) {
            $this->handleException($e, $bookingInspector, 'Request Exception occurred', $e->getMessage());
        } catch (Exception $e) {
            $this->handleException($e, $bookingInspector, 'Unexpected error', $e->getMessage());
        }

        $clientDataResponse = ExpediaHotelBookingRetrieveBookingDto::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse);

        SaveBookingInspector::dispatch($bookingInspector, $dataResponse, $clientDataResponse);

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
        $inspectorCansel = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'cancel_booking', 'true', 'hotel',
        ]);

        $room = $apiBookingsMetadata->supplier_booking_item_id;

        $linkDeleteItem = Arr::get($apiBookingsMetadata->booking_item_data, 'cancellation_paths.0', null);

        if (!$linkDeleteItem) {
            $apiBookingItem = ApiBookingItem::where('booking_item', $apiBookingsMetadata->booking_item)->first();
            $room_id = json_decode($apiBookingItem->booking_item_data, true)['room_id'];
            $linkDeleteItem = BookingRepository::getLinkDeleteItem($filters['booking_id'], $apiBookingsMetadata->booking_item, $room_id)[0];
        }

        // Delete item DELETE method query
        $props = $this->getPathParamsFromLink($linkDeleteItem);
        $path = $props['path'];
        $itineraryId = Arr::get(explode('/', $path), '3', BookingRepository::getItineraryId($filters));

        $bodyArr = [
            'itinerary_id' => $itineraryId,
            'room_id' => $room,
        ];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->delete($path, $props['paramToken'], $body, $this->headers());
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'room' => $room,
                'status' => 'Room canceled.',
            ];
            $dataResponse = json_decode($response->getBody()->getContents(), true) ?? $res;

            SaveBookingInspector::dispatch($inspectorCansel, $dataResponse, $res);
        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $responseErrorArr = json_decode($responseError[1], true);
            $message = $responseErrorArr['message'];
            $res = [
                'booking_item' => $apiBookingsMetadata->booking_item,
                'room' => $room,
                'status' => $message,
            ];
            SaveBookingInspector::dispatch($inspectorCansel, $responseError, $res, 'error',
                ['side' => 'app', 'message' => $message]);
        }

        return $res;
    }

    private function handleException(Exception $e, $bookingInspector, $logMessage, $errorMessage): void
    {
        Log::error($logMessage.': '.$e->getMessage());
        Log::error($e->getTraceAsString());
        SaveBookingInspector::dispatch($bookingInspector, [], [], 'error', ['side' => 'supplier', 'message' => $errorMessage]);
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

    private function saveBookingInfo(array $filters, array $content, ApiBookingInspector $bookingInspector): void
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $roomId = json_decode($bookingInspector->request)?->room_id;

        $filters['supplier_id'] = $supplierId;
        $linkBookRetrieves = Arr::get($content, 'links.retrieve.href');

        $reservation = [
            'bookingId' => $roomId,
            'cancellation_paths' => BookingRepository::getLinkDeleteItem($filters['booking_id'], $filters['booking_item'], $roomId),
            'retrieve_path' => $linkBookRetrieves,
        ];

        SaveBookingMetadata::dispatch($filters, $reservation);
    }
}
