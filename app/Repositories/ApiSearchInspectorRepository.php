<?php

namespace App\Repositories;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\MapperExpediaGiata;
use Illuminate\Support\Facades\Storage;

class ApiSearchInspectorRepository
{

    /**
     * @param string $search_id
     * @return bool
     */
    public static function isMultiple(string $search_id):bool
    {
        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $request = json_decode(Storage::get($search_id->request));

        return count($request['occupancy']) > 1;
    }

    /**
     * @param $filters
     * @return string
     */
    public static function getLinkPriceCheck($filters): string
    {
        $search_id = $filters['search_id'];
        $hotel_id = $filters['hotel_id']; // giata_id
        $room_id = $filters['room_id']; // expedia
        $rate_id = $filters['rate'] ?? ''; // expedia
        $bed_groups = $filters['bed_groups'] ?? ''; // expedia

        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $json_response = json_decode(Storage::get($search_id->response_path));
        $rooms = $json_response->results->Expedia->$hotel_id->rooms;

        $linkPriceCheck = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $rates = $room->rates;
                foreach ($rates as $rate) {
                    if ($rate->id == $rate_id) {
                        if (is_array($rate->bed_groups))
                        {
                            $linkPriceCheck = $rate->bed_groups[$bed_groups]->links->price_check->href;
                        }
                        else
                        {
                            $linkPriceCheck = $rate->bed_groups->$bed_groups->links->price_check->href;
                        }
                    }
                }
                break;
            }
        }

        return $linkPriceCheck;
    }

    /**
     * @param string $search_id
     * @return string
     */
    public static function geTypeBySearchId(string $search_id): string
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();
        return $search->search_type;
    }

    /**
     * @param string $search_id
     * @return array
     */
    public static function getRequest(string $search_id): array
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();
        return $search?->request ? json_decode($search->request, true) : [];
    }

    public static function getResponse(string $search_id): array
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();
        return $search->response_path ? json_decode(Storage::get($search->response_path), true) : [];
    }

    /**
     * @param $filters
     * @return array
     */
    public function getReservationsDataBySearchId($filters): array
    {
        $search_id = $filters['search_id'];
        $hotel_id = $filters['hotel_id']; // giata_id

        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $json_response = json_decode(Storage::get($search_id->client_response_path));

        $hotels = $json_response->results->Expedia;

        $price = [];
        foreach ($hotels as $hotel) {
            if ($hotel->giata_hotel_id != $hotel_id) continue;
            $hotel_id = $hotel->supplier_hotel_id;
            foreach ($hotel->room_groups as $room) {
                $price = [
                    'total_price' => $room->total_price,
                    'total_tax' => $room->total_tax,
                    'total_fees' => $room->total_fees,
                    'total_net' => $room->total_net,
                    'currency' => $room->currency,
                ];
            }
        }

        return ['query' => $json_response->query, 'price' => $price, 'supplier_hotel_id' => $hotel_id];
    }

    /**
     * @param ApiBookingItem $apiBookingItem
     * @param ApiSearchInspector $searchInspector
     * @return array
     */
    public static function getReservationsData(ApiBookingItem $apiBookingItem, ApiSearchInspector $searchInspector): array
    {
        $booking_item_data = json_decode($apiBookingItem->booking_item_data, true);
        $client_response = json_decode(Storage::get($searchInspector->client_response_path), true);

        $price = json_decode($apiBookingItem->booking_pricing_data, true);

        $expedia_hotel_id = MapperExpediaGiata::where('giata_id', $booking_item_data['hotel_id'])?->first()?->expedia_id;
        return [
            'query' => $client_response['query'],
            'price' => $price,
            'expedia_hotel_id' => $expedia_hotel_id,
            'hotel_id' => $booking_item_data['hotel_id'],
        ];
    }

    /**
     * @return int
     */
    public function getTotalOccupancyAttribute(): int
    {
        $occupancy = $this->request['occupancy'] ?? [];

        $totalAdults = 0;
        $totalChildren = 0;

        foreach ($occupancy as $occupant) {
            $totalAdults += $occupant['adults'];
            $totalChildren += $occupant['children'] ?? 0;
        }

        return $totalAdults + $totalChildren;
    }

    /**
     * @return int
     */
    public function getTotalChildrenAttribute(): int
    {
        $occupancy = $this->request['occupancy'] ?? [];

        $totalChildren = 0;

        foreach ($occupancy as $occupant) {
            $totalChildren += $occupant['children'] ?? 0;
        }

        return $totalChildren;
    }

    public static function newSearchInspector(array $input): array
    {
        /**
         * @param string $search_id
         * @param string $type
         * @param string $search_type
         * @param string $suppliers
         * @param array $request
         */
        [$search_id, $request, $suppliers, $type, $search_type] = $input;

        $token_id = ChannelRenository::getTokenId(request()->bearerToken());

        $inspector = new ApiSearchInspector();
        $inspector->search_id = $search_id;
        $inspector->type = $type;
        $inspector->search_type = $search_type;
        $inspector->suppliers = implode(',', $suppliers);
        $inspector->request = json_encode($request);
        $inspector->token_id = $token_id;

        \Log::info('Created ApiSearchInspector:', ['inspector' => $inspector]);

        return $inspector->toArray();


    }
}
