<?php

namespace Modules\API\BookingAPI;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Channel;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class ExpediaBookApiHandler extends BaseController
{
    /**
     * @var RapidClient
     */
    private RapidClient $rapidClient;

    /**
     * @param ExpediaService $expediaService
     */
    public function __construct()
    {
        $this->rapidClient = new RapidClient();
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function changeBooking(array $filters): array | null
    {
        # step 1 Get room_id from ApiBookingItem
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $room_id = json_decode($bookingItem->booking_item_data, true)['room_id'];

        # step 2 Read Booking Inspector, Get link  PUT method from 'add_item | get_book'
        $inspector = new ApiBookingInspector();
        $linkPutMethod = $inspector->getLinkPutMethod($filters['booking_id'], $room_id);

        $search_id = $inspector->getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];

        # Booking PUT query
        $props = $this->getPathParamsFromLink($linkPutMethod);

        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
        ];

        $bodyArr = $filters['query'];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaBookApiHandler | addPassengers | Booking PUT query ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
        }

        if (!$dataResponse) {
            return [];
        }

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $dataResponse,
            1,
            'change_booking',
            '',
            'hotel',
        ]);

        return (array) $dataResponse;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function book(array $filters, ApiBookingInspector $bookingInspector): array | null
    {

        $booking_id = $bookingInspector->booking_id;

        $queryHold = $filters['query']['hold'] ?? false;

        $dataResponse = json_decode(Storage::get($bookingInspector->response_path));

        $linkBookItineraries = $dataResponse->links->book->href;
        $filters['search_id'] = $bookingInspector->search_id;
        $filters['booking_item'] = $bookingInspector->booking_item;

        $passengers = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $filters['booking_item'])
            ->where('type', 'add_passengers')
            ->first();

        if (!$passengers) {
            return [
                'error' => 'Passengers not found.',
                'booking_item' => $filters['booking_item'],
            ];
        } else {
            $passengersArr = $passengers->toArray();
        }

        $dataPassengers = json_decode($passengersArr['request'], true);

        # Booking POST query - Create Booking
        // TODO: need check count of rooms. count(rooms) in current query == count(rooms) in search query
        $props = $this->getPathParamsFromLink($linkBookItineraries);

        $bodyArr = $dataPassengers;

        $bodyArr['affiliate_reference_id'] = 'UJV_' . time();

        // TODO: need move it to config or const
        foreach ($bodyArr['payments'] as $key => $payment) {
            $bodyArr['payments'][$key]['type'] = 'affiliate_collect';
        }

        $body = json_encode($bodyArr);
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
        ];
        try {
            $response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaBookApiHandler | book | create' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array) $dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            [],
            1,
            'book',
            'create' . ($queryHold ? ':hold' : ''),
            $bookingInspector->search_type,	// hotel | flight | combo
        ]);

        $itinerary_id = $dataResponse->itinerary_id;
        $linkBookRetrieves = $dataResponse->links->retrieve->href;

        # Booking GET query - Retrieve Booking
        $props = $this->getPathParamsFromLink($linkBookRetrieves);
        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaBookApiHandler | book | retrieve ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array) $dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) {
            $res = (array) $dataResponse;
        } else {
            $res = [
                'booking_id' => $booking_id,
                'search_id' => $filters['search_id'],
                'booking_item' => $filters['booking_item'],
                'links' => [
                    'remove' => [
                        'method' => 'DELETE',
                        'href' => '/api/booking/cancel-booking?booking_id=' . $booking_id . '&booking_item=' . $filters['booking_item'],
                    ],
                    'change' => [
                        'method' => 'PUT',
                        'href' => '/api/booking/change-booking?booking_id=' . $booking_id . '&booking_item=' . $filters['booking_item'],
                    ],
                    'retrieve' => [
                        'method' => 'GET',
                        'href' => '/api/booking/retrieve-booking?booking_id=' . $booking_id,
                    ],
                ],
            ];
        }

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $res,
            1,
            'book',
            'retrieve' . ($queryHold ? ':hold' : ''),
            $bookingInspector->search_type,	// hotel | flight | combo
        ]);

        return $res;
    }

    /**
     * @return array|null
     */
    public function listBookings(): array | null
    {
        $ch = new Channel;
        $token_id = $ch->getTokenId(request()->bearerToken());

        # step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $inspector = new ApiBookingInspector();
        $list = $inspector->getAffiliateReferenceIdByChannel($token_id);
        $path = '/v3/itineraries';
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
        ];

        foreach ($list as $item) {
            try {
                $promises[] = $this->rapidClient->getAsync($path, $item, $addHeaders);
            } catch (Exception $e) {
                \Log::error('Error while creating promise: ' . $e->getMessage());
            }
        }
        $responses = [];
        $resolvedResponses = Promise\Utils::settle($promises)->wait();
        foreach ($resolvedResponses as $response) {
            if ($response['state'] === 'fulfilled') {
                $data = $response['value']->getBody()->getContents();
                $responses[] = json_decode($data, true);
            } else {
                \Log::error('ExpediaBookApiHandler | listBookings  failed: ' . $response['reason']->getMessage());
            }
        }

        return $responses;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function retrieveBooking(array $filters, ApiBookingInspector $bookingInspector): array | null
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = $bookingInspector->search_id;
		$filters['booking_item'] = $bookingInspector->booking_item;
        $dataResponse = [];
        $json_response = json_decode(Storage::get($bookingInspector->response_path));

        $linkRetrieveItem = $json_response->links->retrieve->href;

        $props = $this->getPathParamsFromLink($linkRetrieveItem);
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
        ];
        $response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
        $dataResponse = json_decode($response->getBody()->getContents());

        // TODO: need create DTO for $clientDataResponse
        $clientDataResponse = $dataResponse;

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $clientDataResponse,
            1,
            'retrieve_booking',
            '',
            $bookingInspector->search_type, // hotel | flight | combo
        ]);

        return (array) $dataResponse;
    }

    /**
     * @param array $filters
     * @return array|null
     */

    public function cancelBooking(array $filters, ApiBookingInspector $bookingInspector): array | null
    {
        # step 1 Get room_id from ApiBookingItem
		$apiBookingItem = ApiBookingItem::where('booking_item', $bookingInspector->booking_item)->first();
        $room_id = json_decode($apiBookingItem->booking_item_data, true)['room_id'];

        # step 2 Read Booking Inspector, Get link  DELETE method from 'add_item | get_book'
        $inspector = new ApiBookingInspector();
        $linkDeleteItem = $inspector->getLinkDeleteItem($filters['booking_id'], $bookingInspector->booking_item, $room_id);

        $filters['search_id'] = $bookingInspector->search_id;
        $booking_id = $filters['booking_id'];

        # Delete item DELETE method query
        $props = $this->getPathParamsFromLink($linkDeleteItem);

        $bodyArr = [
            'itinerary_id' => $inspector->getItineraryId($filters),
            'room_id' => $room_id,
        ];
        $body = json_encode($bodyArr);


        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
        ];
		$dataResponse = [];
        try {
            $response = $this->rapidClient->delete($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());

            $res = [
                'booking_item' => $bookingInspector->booking_item,
                'status' => 'Room cancelled.',
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

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $res,
            1,
            'cancel_booking',
            'true',
            $bookingInspector->search_type,	// hotel | flight | combo
        ]);

        return $res;
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function retrieveItem(array $filters, ApiBookingInspector $bookingInspector): array | null
    {
        $data = [];

        $apiBookingItem = ApiBookingItem::where('booking_item', $bookingInspector->booking_item)->first();
        $booking_item_data = json_decode($apiBookingItem->booking_item_data, true);

        $searchInspector = ApiSearchInspector::where('search_id', $bookingInspector->search_id)->first();
        $client_response = json_decode(Storage::get($searchInspector->client_response_path), true);

        $response = [];
        foreach ($client_response['results']['Expedia'] as $value) {
            if ($value['giata_hotel_id'] === $booking_item_data['hotel_id']) {
                $itemData = $value;
            }
        }

        if ($bookingInspector->search_type == 'hotel') {
            foreach ($itemData['room_groups'] as $kg => $group) {
                foreach ($group['rooms'] as $kr => $room) {
                    if ($room['booking_item'] === $bookingInspector->booking_item) {
                        $data = $room;
                    }
                }
            }
        }

        return [
            'booking_id' => $bookingInspector->booking_id,
            'booking_item' => $bookingInspector->booking_item,
            'search_id' => $bookingInspector->search_id,
            'booking_item_data' => $booking_item_data,
            'data' => $data,
            'request' => json_decode($searchInspector->request, true),
        ];
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
}
