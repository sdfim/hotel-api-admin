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
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
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
        private readonly RapidClient         $rapidClient = new RapidClient(),
        private readonly ExpediaHotelBookDto $expediaBookDto = new ExpediaHotelBookDto(),
    )
    {
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function changeBooking(array $filters): array|null
    {
        # step 1 Get room_id from ApiBookingItem
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $room_id = json_decode($bookingItem->booking_item_data, true)['room_id'];

        # step 2 Read Booking Inspector, Get link  PUT method from 'add_item | get_book'
        $linkPutMethod = BookingRepository::getLinkPutMethod($filters['booking_id'], $room_id);

        $search_id = BookingRepository::getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];

        # Booking PUT query
        $props = $this->getPathParamsFromLink($linkPutMethod);
        $bodyArr = $filters['query'];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            Log::error('ExpediaBookApiHandler | changeBooking | Booking PUT query ' . $e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
        }

        if (!$dataResponse) {
            return [];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponse, $dataResponse, $supplierId, 'change_booking', '', 'hotel',
        ]);

        return (array)$dataResponse;
    }

    /**
     * @param array $filters
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     * @throws Exception
     */
    public function book(array $filters, ApiBookingInspector $bookingInspector): array|null
    {
        $booking_id = $bookingInspector->booking_id;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;

        $passengers = BookingRepository::getPassengers($booking_id, $filters['booking_item']);

        if (!$passengers) {
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

        $bodyArr['affiliate_reference_id'] = 'UJV_' . time();

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

        try {
            $response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $this->headers());

            $content = json_decode($response->getBody()->getContents(), true);
            $content['original']['response'] = $content;
            $content['original']['request']['params'] = $props['paramToken'];
            $content['original']['request']['body'] = json_decode($body,true);
            $content['original']['request']['path'] = $props['path'];
            $content['original']['request']['headers'] = $this->headers();
        } catch (RequestException $e) {
            Log::error('ExpediaBookApiHandler | book | create' . $e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $content = json_decode('' . $e->getResponse()->getBody());
            return (array)$content;
        }

        if (!$content) {
            return [];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, array_merge($filters, $bodyArr), $content, [], $supplierId, 'book',
            'create' . ($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

        # Save Book data to Reservation
        SaveReservations::dispatch($booking_id, $filters, $dataPassengers);

        $linkBookRetrieves = $content['links']['retrieve']['href'];

        # Booking GET query - Retrieve Booking
        $props = $this->getPathParamsFromLink($linkBookRetrieves);

        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents(), true);
            $confirmationNumbers = [
                'confirmation_number' => $dataResponse['itinerary_id'] ?? '',
                'type' => SupplierNameEnum::EXPEDIA->value,
            ];
            $clientResponse = $dataResponse ? $this->expediaBookDto->toHotelBookResponseModel($filters, $confirmationNumbers) : [];
        } catch (RequestException $e) {
            Log::error('ExpediaBookApiHandler | book | retrieve ' . $e->getResponse()->getBody());
            Log::error($e->getTraceAsString());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = $dataResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponse, $res, $supplierId, 'book',
            'retrieve' . ($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

        $this->saveBookingInfo($filters, $content, $bookingInspector);

        return $res;
    }

    /**
     * @return array|null
     */
    public function listBookings(): array|null
    {
        $token_id = ChannelRenository::getTokenId(request()->bearerToken());

        # step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $list = BookingRepository::getAffiliateReferenceIdByChannel($token_id);
        $path = '/v3/itineraries';

        $promises = [];
        foreach ($list as $item) {
            try {
                $promises[] = $this->rapidClient->getAsync($path, $item, $this->headers());
            } catch (Exception $e) {
                Log::error('Error while creating promise: ' . $e->getMessage());
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
                Log::error('ExpediaBookApiHandler | listBookings  failed: ' . $response['reason']->getMessage());
                Log::error($e->getTraceAsString());
            }
        }

        return $responses;
    }

    /**
     * @param array $filters
     * @param ApiBookingsMetadata $apiBookingsMetadata
     * @return array|null
     */
    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): array|null
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = '';
        $filters['booking_item'] = $apiBookingsMetadata->booking_item;

        $props = $this->getPathParamsFromLink($apiBookingsMetadata->booking_item_data['retrieve_path']);
        $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
        $dataResponse = json_decode($response->getBody()->getContents(), true);
        $dataResponse['original']['response'] = $dataResponse;
        $dataResponse['original']['request']['params'] = $props['paramToken'];
        $dataResponse['original']['request']['path'] = $props['path'];
        $dataResponse['original']['request']['headers'] = $this->headers();

        $clientDataResponse = ExpediaHotelBookingRetrieveBookingDto::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse);

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponse, $clientDataResponse, $supplierId, 'retrieve_booking',
            '', $apiBookingsMetadata->search_type,
        ]);

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return (array)$dataResponse;
        } else {
            return $clientDataResponse;
        }
    }

    /**
     * @param array $filters
     * @param ApiBookingsMetadata $apiBookingsMetadata
     * @return array|null
     * @throws GuzzleException
     */
    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): array|null
    {
        $room = $apiBookingsMetadata->supplier_booking_item_id;
        $cancellationPaths = $apiBookingsMetadata->booking_item_data['cancellation_paths'];

        foreach ($cancellationPaths as $cancellationPath)
        {
            # Delete item DELETE method query
            $props = $this->getPathParamsFromLink($cancellationPath);
            $path = $props['path'];
            $itineraryId = Arr::get(explode('/', $path), '3', BookingRepository::getItineraryId($filters));

            $bodyArr = [
                'itinerary_id' => $itineraryId,
                'room_id' => $room,
            ];
            $body = json_encode($bodyArr);

            try {
                $response = $this->rapidClient->delete($path, $props['paramToken'], $body, $this->headers());
                $dataResponse = json_decode($response->getBody()->getContents());
                $res[] = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'room' => $room,
                    'status' => 'Room canceled.',
                ];

            } catch (Exception $e) {
                $responseError = explode('response:', $e->getMessage());
                $responseErrorArr = json_decode($responseError[1], true);
                $res[] = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'room' => $room,
                    'status' => $responseErrorArr['message'],
                ];
                $dataResponse = $responseErrorArr['message'];
            }

            $filters['booking_item'] = $apiBookingsMetadata->booking_item;

            $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
            SaveBookingInspector::dispatch([
                $apiBookingsMetadata->booking_id, $filters, $dataResponse, $res, $supplierId, 'cancel_booking',
                'true', 'hotel',
            ]);
        }

        return $res;
    }

    /**
     * @param string $link
     * @return array
     */
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
            'Test' => 'standard',
        ];
    }

    private function saveBookingInfo(array $filters, array $content, ApiBookingInspector $bookingInspector): void
    {
        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        $roomId = json_decode($bookingInspector->request)?->room_id;

        $filters['supplier_id'] = $supplierId;
        $linkBookRetrieves = Arr::get($content, 'links.retrieve.href');

        $reservation = [
            'bookingId'           => $roomId,
            'cancellation_paths'  => BookingRepository::getLinkDeleteItem($filters['booking_id'], $filters['booking_item'], $roomId),
            'retrieve_path'      => $linkBookRetrieves,
        ];

        SaveBookingMetadata::dispatch($filters, $reservation);
    }
}
