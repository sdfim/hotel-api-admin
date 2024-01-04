<?php

namespace Modules\API\BookingAPI;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiSearchInspector;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Illuminate\Support\Facades\Storage;


class ExpediaHotelBookingApiHandler
{
    /**
     * @var RapidClient
     */
    private RapidClient $rapidClient;


    public function __construct()
    {
        $this->rapidClient = new RapidClient();
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function addItem(array $filters): array|null
    {
        # step 1 Read Inspector, Get link 'price_check'
        $linkPriceCheck = SearchRepository::getLinkPriceCheck($filters);

        # step 2 Get POST link for booking
        // TODO: need check if price changed
        $props = $this->getPathParamsFromLink($linkPriceCheck);
        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken']);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            Log::error('ExpediaHotelBookingApiHandler | addItem | price_check ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array)$dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        $booking_id = $filters['booking_id'] ?? (string)Str::uuid();

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            [],
            1,
            'add_item',
            'price_check',
            'hotel',
        ]);

        return ['booking_id' => $booking_id];
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
        $filters['search_id'] = ApiBookingInspector::where('booking_id', $filters['booking_id'])->first()->search_id;
        $booking_id = $filters['booking_id'];
        $booking_item = $filters['booking_item'];

        try {

            $bookItems = ApiBookingInspector::where('booking_id', $booking_id)
                ->where('type', 'book')
                ->get()->pluck('booking_id')->toArray();

            $bookingItems = ApiBookingInspector::where('booking_item', $booking_item)
                ->where('type', 'add_item')
                ->whereNotIn('booking_id', $bookItems);

            if ($bookingItems->get()->count() === 0) {
                $res = [
                    'success' =>
                        [
                            'booking_id' => $booking_id,
                            'booking_item' => $booking_item,
                            'status' => 'This item is not in the cart',
                        ]
                ];
            } else {
                foreach ($bookingItems->get() as $item) {
                    Storage::delete($item->client_response_path);
                    Storage::delete($item->response_path);
                }

                ApiBookingInspector::where('booking_id', $booking_id)
                    ->whereIn('booking_item', $bookingItems->get()->pluck('booking_item')->toArray())
                    ->where('type', 'add_passengers')->delete();

                $bookingItems->delete();

                $res = [
                    'success' =>
                        [
                            'booking_id' => $booking_id,
                            'booking_item' => $booking_item,
                            'status' => 'Item removed from cart.',
                        ]
                ];
            }
        } catch (Exception $e) {
            $res = [
                'error' => [
                    'booking_id' => $booking_id,
                    'booking_item' => $booking_item,
                    'status' => 'Item not removed from cart.',
                ]
            ];
            Log::error('ExpediaHotelBookingApiHandler | removeItem | ' . $e->getMessage());
        }

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            [],
            $res,
            1,
            'remove_item',
            '',
            'hotel',
        ]);

        return $res;
    }

}
