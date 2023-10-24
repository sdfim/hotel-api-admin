<?php

namespace Modules\API\BookingAPI;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiSearchInspector;
use App\Models\ApiBookingInspector;
use App\Models\Channel;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Inspector\BookingInspectorController;
use Illuminate\Support\Str;
use GuzzleHttp\Promise;

class ExpediaHotelBookingApiHandler
{
    /**
     *
     */
    private const AFFILIATE_REFERENCE_ID = 'UJV-V1';
    /**
     * @var ExpediaService
     */
    private ExpediaService $expediaService;
    /**
     * @var BookingInspectorController
     */
    private BookingInspectorController $bookingInspector;
    /**
     * @var RapidClient
     */
    private RapidClient $rapidClient;

    /**
     * @param ExpediaService $expediaService
     */
    public function __construct(ExpediaService $expediaService)
    {
        $this->expediaService = $expediaService;
        $this->bookingInspector = new BookingInspectorController();
        $this->rapidClient = new RapidClient(env('EXPEDIA_RAPID_API_KEY'), env('EXPEDIA_RAPID_SHARED_SECRET'));
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function addItem(array $filters): array|null
    {
        $queryHold = $filters['query']['hold'];
        # step 1 Read Inspector, Get link 'price_check'
        $inspector = new ApiSearchInspector();
        $linkPriceCheck = $inspector->getLinckPriceCheck($filters);

        # step 2 Get POST link for booking
        // TODO: need check if price changed
        $props = $this->getPathParamsFromLink($linkPriceCheck);
        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken']);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaHotelBookingApiHandler | addItem | price_check ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) return [];
        $booking_id = (string)Str::uuid();

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            [],
            1,
            'add_item',
            'price_check' . ($queryHold ? ':hold' : ''),
            'hotel'
        ]);

        $linkBookItineraries = $dataResponse->links->book->href;

        # Booking POST query - Create Booking
        // TODO: need check count of rooms. count(rooms) in current query == count(rooms) in search query
        $props = $this->getPathParamsFromLink($linkBookItineraries);
        $bodyArr = $filters['query'];
        $bodyArr['affiliate_reference_id'] = 'UJV_' . time();
        $body = json_encode($bodyArr);
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard'
        ];
        try {
            $response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaHotelBookingApiHandler | addItem | create ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) return [];
        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            [],
            1,
            'add_item',
            'create' . ($queryHold ? ':hold' : ''),
            'hotel'
        ]);

        $itinerary_id = $dataResponse->itinerary_id;
        $linkBookRetrieves = $dataResponse->links->retrieve->href;

        # Booking GET query - Retrieve Booking
        $props = $this->getPathParamsFromLink($linkBookRetrieves);
        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaHotelBookingApiHandler | addItem | create ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) return [];

        $viewSupplierData = $filters['supplier_data'] ?? false;
        if ($viewSupplierData) $res = (array)$dataResponse;
        else $res = [
            'booking_id' => $booking_id,
            'search_id' => $filters['search_id'],
            'links' => [
                'remove' => [
                    'method' => 'DELETE',
                    'href' => '/api/booking/remove-item?booking_id=' . $booking_id . '&room_id=' . $filters['room_id'],
                ],
                'change' => [
                    'method' => 'PUT',
                    'href' => '/api/booking/change-items?booking_id=' . $booking_id . '&room_id=' . $filters['room_id'],
                ],
                'retrieve' => [
                    'method' => 'GET',
                    'href' => '/api/booking/retrieve-items?booking_id=' . $booking_id,
                ],
            ],
        ];

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $res,
            1,
            'add_item',
            'retrieve' . ($queryHold ? ':hold' : ''),
            'hotel'
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
     * @param array $filters
     * @return array
     */
    public function removeItem(array $filters): array
    {
        # step 1 Read Booking Inspector, Get link  DELETE method from 'add_item | get_book'
        $inspector = new ApiBookingInspector();
        $linkDeleteItem = $inspector->getLinkDeleteItem($filters);
        $search_id = $inspector->getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];

        # Delete item DELETE method query
        $props = $this->getPathParamsFromLink($linkDeleteItem);

        // dump($props, $linkDeleteItem);

        $bodyArr = [
            'itinerary_id' => $inspector->getItineraryId($filters),
            'room_id' => $filters['room_id']
        ];
        $body = json_encode($bodyArr);

        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard'
        ];
        try {
            $response = $this->rapidClient->delete($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());

            if (!$dataResponse) {
                SaveBookingInspector::dispatch([
                    $booking_id,
                    $filters,
                    $dataResponse,
                    ['success' => 'Room cancelled.'],
                    1,
                    'remove_item',
                    'true',
                    'hotel'
                ]);
            }

            return ['success' => 'Room cancelled.'];
        } catch (Exception $e) {
            $responseError = explode('response:', $e->getMessage());
            $responseErrorArr = json_decode($responseError[1], true);
            SaveBookingInspector::dispatch([
                $booking_id,
                $filters,
                $responseErrorArr,
                ['error' => 'Room is already cancelled.'],
                1,
                'remove_item',
                'false',
                'hotel'
            ]);
            return ['error' => $responseErrorArr['message']];
        }
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function retrieveItems(array $filters): array|null
    {
        $booking_id = $filters['booking_id'];

        # step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $inspector = new ApiBookingInspector();
        $linkDeleteItem = $inspector->getLinkRetrieveItem($booking_id);

        $search_id = $inspector->getSearchId($filters);
        $filters['search_id'] = $search_id;

        # Booking GET query
        $props = $this->getPathParamsFromLink($linkDeleteItem);
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard'
        ];
        $response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
        $dataResponse = json_decode($response->getBody()->getContents());

        // TODO: need create DTO for $clientDataResponse
        $clientDataResponse = $dataResponse;

        if (!$dataResponse) return [];
        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $clientDataResponse,
            1,
            'retrieve_items',
            '',
            'hotel'
        ]);

        return (array)$dataResponse;
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function addPassengers(array $filters): array|null
    {
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function changeItems(array $filters): array|null
    {
        # step 1 Read Booking Inspector, Get link  PUT method from 'add_item | get_book'
        $inspector = new ApiBookingInspector();
        $linkPutMethod = $inspector->getLinkPutMethod($filters);
        $search_id = $inspector->getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];

        # Booking PUT query
        $props = $this->getPathParamsFromLink($linkPutMethod);
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard'
        ];

        $bodyArr = $filters['query'];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaHotelBookingApiHandler | addPassengers | Booking PUT query ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
        }

        if (!$dataResponse) return [];
        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $dataResponse,
            1,
            'change_items',
            '',
            'hotel'
        ]);

        return (array)$dataResponse;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function book(Request $request): array|null
    {
    }

    /**
     * @return array|null
     */
    public function listBookings(): array|null
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
            'Test' => 'standard'
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
                \Log::error('ExpediaHotelBookingApiHandler | listBookings  failed: ' . $response['reason']->getMessage());
            }
        }

        return $responses;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function retrieveBooking(Request $request): array|null
    {
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function cancelBooking(array $filters): array|null
    {
    }
}
