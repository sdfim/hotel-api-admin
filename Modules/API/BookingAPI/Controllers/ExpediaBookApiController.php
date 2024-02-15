<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Jobs\SaveReservations;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ChannelRenository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelBookDto;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelBookingRetrieveBookingDto;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class ExpediaBookApiController extends BaseBookApiController
{
    private const PAYMENTS_TYPE = 'affiliate_collect';

    public function __construct(
        private readonly RapidClient         $rapidClient = new RapidClient(),
        private readonly ExpediaHotelBookDto $expediaBookDto = new ExpediaHotelBookDto(),
    ) {}

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
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            Log::error('ExpediaBookApiHandler | book | create' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, array_merge($filters, $bodyArr), $dataResponse, [], $supplierId, 'book',
            'create' . ($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

        # Save Book data to Reservation
        SaveReservations::dispatch($booking_id, $filters, $dataPassengers);

        $linkBookRetrieves = $dataResponse->links->retrieve->href;

        # Booking GET query - Retrieve Booking
        $props = $this->getPathParamsFromLink($linkBookRetrieves);

        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents());
            $clientResponse = $dataResponse ? $this->expediaBookDto->toHotelBookResponseModel($filters) : [];
        } catch (RequestException $e) {
            Log::error('ExpediaBookApiHandler | book | retrieve ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = (array)$dataResponse;
        } else {
            $res = $clientResponse + $this->tailBookResponse($booking_id, $filters['booking_item']);
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponse, $res, $supplierId, 'book',
            'retrieve' . ($queryHold ? ':hold' : ''), $bookingInspector->search_type,
        ]);

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
            }
        }

        return $responses;
    }

    /**
     * @param array $filters
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     */
    public function retrieveBooking(array $filters, ApiBookingInspector $bookingInspector): array|null
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;
        $json_response = json_decode(Storage::get($bookingInspector->response_path));

        $linkRetrieveItem = $json_response->links->retrieve->href;
        $props = $this->getPathParamsFromLink($linkRetrieveItem);
        $response = $this->rapidClient->get($props['path'], $props['paramToken'], $this->headers());
        $dataResponse = json_decode($response->getBody()->getContents(), true);

        $clientDataResponse = ExpediaHotelBookingRetrieveBookingDto::RetrieveBookingToHotelBookResponseModel($filters, $dataResponse);

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponse, $clientDataResponse, $supplierId, 'retrieve_booking',
            '', $bookingInspector->search_type,
        ]);

        if (isset($filters['supplier_data']) && $filters['supplier_data'] == 'true') {
            return (array)$dataResponse;
        } else {
            return $clientDataResponse;
        }
    }

    /**
     * @param array $filters
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     * @throws GuzzleException
     */
    public function cancelBooking(array $filters, ApiBookingInspector $bookingInspector): array|null
    {
        # step 1 Get room_id from ApiBookingItem
        $apiBookingItem = ApiBookingItem::where('booking_item', $bookingInspector->booking_item)->first();
        $room_id = json_decode($apiBookingItem->booking_item_data, true)['room_id'];

        # step 2 Read Booking Inspector, Get link  DELETE method from 'add_item | get_book'
        $linkDeleteItem = BookingRepository::getLinkDeleteItem($filters['booking_id'], $bookingInspector->booking_item, $room_id);

        $filters['search_id'] = $bookingInspector->search_id;
        $booking_id = $filters['booking_id'];

        # Delete item DELETE method query
        $props = $this->getPathParamsFromLink($linkDeleteItem);

        $bodyArr = [
            'itinerary_id' => BookingRepository::getItineraryId($filters),
            'room_id' => $room_id,
        ];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->delete($props['path'], $props['paramToken'], $body, $this->headers());
            $dataResponse = json_decode($response->getBody()->getContents());

            $res = [
                'booking_item' => $bookingInspector->booking_item,
                'status' => 'Room canceled.',
            ];

        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $responseErrorArr = json_decode($responseError[1], true);
            $res = [
                'booking_item' => $bookingInspector->booking_item,
                'status' => $responseErrorArr['message'],
            ];
            $dataResponse = $responseErrorArr['message'];
        }

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, $dataResponse, $res, $supplierId, 'cancel_booking',
            'true', $bookingInspector->search_type,
        ]);

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

    /**
     * @param array $filters
     * @param array $passengersData
     * @return array|null
     */
    public function addPassengers(array $filters, array $passengersData): array|null
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = ApiBookingInspector::where('booking_item', $filters['booking_item'])->first()->search_id;

        $bookingItem = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $filters['booking_item'])
            ->where('type', 'add_passengers');

        $apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->request;

        $countRooms = count(json_decode($apiSearchInspector, true)['occupancy']);

        $type = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->search_type;
        if (TypeRequestEnum::from($type) === TypeRequestEnum::HOTEL)
            for ($i = 1; $i <= $countRooms; $i++) {
                $filters['rooms'][] = $passengersData['rooms'][$i]['passengers'];
            }

        if ($bookingItem->get()->count() > 0) {
            $bookingItem->delete();
            $status = 'Passengers updated to booking.';
            $subType = 'updated';
        } else {
            $status = 'Passengers added to booking.';
            $subType = 'add';
        }

        $res = [
            'booking_id' => $booking_id,
            'booking_item' => $filters['booking_item'],
            'status' => $status,
        ];

        $supplierId = Supplier::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, [], $res, $supplierId, 'add_passengers', $subType, 'hotel',
        ]);

        return $res;
    }
}
