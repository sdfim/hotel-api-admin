<?php

namespace App\Repositories;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Mapping;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ApiSearchInspectorRepository
{
    public static function isMultiple(string $search_id): bool
    {
        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $request = json_decode(Storage::get($search_id->request));

        return count($request['occupancy']) > 1;
    }

    public static function getLinkPriceCheck($filters): string
    {
        $search_id = $filters['search_id'];
        $hotel_id = $filters['hotel_id']; // giata_id
        $room_id = $filters['room_id']; // expedia
        $rate_id = $filters['rate'] ?? ''; // expedia
        $bed_groups = $filters['bed_groups'] ?? ''; // expedia
        $booking_item = $filters['booking_item'];

        $bookingItemData = ApiBookingItemRepository::getItemDataCache($booking_item);
        $keyExpedia = 'Expedia_'.$bookingItemData['query_package'];

        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $json_response = json_decode(Storage::get($search_id->response_path));
        $rooms = $json_response->results->$keyExpedia->$hotel_id->rooms;

        $linkPriceCheck = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $rates = $room->rates;
                foreach ($rates as $rate) {
                    if ($rate->id == $rate_id) {
                        if (is_array($rate->bed_groups)) {
                            $linkPriceCheck = $rate->bed_groups[$bed_groups]->links->price_check->href;
                        } else {
                            $linkPriceCheck = $rate->bed_groups->$bed_groups->links->price_check->href;
                        }
                    }
                }
                break;
            }
        }

        return $linkPriceCheck;
    }

    public static function getLinkAvailability($search_id, ApiBookingItem $bookingItem): ?string
    {
        try {
            $searchId = ApiSearchInspector::where('search_id', $search_id)->first();
            $hotel_id = Arr::get(json_decode($bookingItem->booking_item_data, true), 'hotel_id'); // giata
            $json_response = json_decode(Storage::get($searchId->response_path));
            $link = $json_response->results->Expedia->$hotel_id->links->additional_rates->href;
        } catch (\Exception $e) {
            return null;
        }

        return $link;
    }

    public static function geTypeBySearchId(string $search_id): string
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();

        return $search->search_type;
    }

    public static function getRequest(string $search_id): array
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();

        return $search?->request ? json_decode($search->request, true) : [];
    }

    public static function getSearchInLoop(string $searchId): ApiSearchInspector
    {
        $search = null;
        $maxAttempts = 10;
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            $search = ApiSearchInspector::where('search_id', $searchId)->first();
            if ($search) {
                break;
            }
            sleep(1);
            $attempt++;
        }

        return $search;
    }

    public static function getResponse(string $search_id): array
    {
        $search = ApiSearchInspector::where('search_id', $search_id)->first();

        return $search->response_path ? json_decode(Storage::get($search->response_path), true) : [];
    }

    public function getReservationsDataBySearchId($filters): array
    {
        $search_id = $filters['search_id'];
        $hotel_id = $filters['hotel_id']; // giata_id

        $search_id = ApiSearchInspector::where('search_id', $search_id)->first();
        $json_response = json_decode(Storage::get($search_id->client_response_path));

        $hotels = $json_response->results->Expedia;

        $price = [];
        foreach ($hotels as $hotel) {
            if ($hotel->giata_hotel_id != $hotel_id) {
                continue;
            }
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

    public static function getReservationsData(ApiBookingItem $apiBookingItem, ApiSearchInspector $searchInspector): array
    {
        $booking_item_data = json_decode($apiBookingItem->booking_item_data, true);
        $client_response = json_decode(Storage::get($searchInspector->client_response_path), true);

        $price = json_decode($apiBookingItem->booking_pricing_data, true);

        $expedia_hotel_id = Mapping::expedia()->where('giata_id', $booking_item_data['hotel_id'])?->first()?->supplier_id;

        return [
            'query' => $client_response['query'],
            'price' => $price,
            'expedia_hotel_id' => $expedia_hotel_id,
            'hotel_id' => $booking_item_data['hotel_id'],
        ];
    }

    public static function getTotalOccupancy(array $occupancy): int
    {
        $numberOfChildren = 0;
        $numberOfAdults = 0;

        foreach ($occupancy as $room) {
            if (! empty($room['children_ages'])) {
                $numberOfChildren += count($room['children_ages']);
            }

            $numberOfAdults += $room['adults'];
        }

        return $numberOfChildren + $numberOfAdults;
    }

    public static function getOccupancyBreakdown(array $occupancy): array
    {
        $breakdown = [
            'children' => 0,
            'adults' => 0,
        ];

        foreach ($occupancy as $room) {
            if (! empty($room['children_ages'])) {
                $breakdown['children'] += count($room['children_ages']);
            }

            $breakdown['adults'] += $room['adults'] ?? 0;
        }

        $breakdown['total'] = $breakdown['children'] + $breakdown['adults'];

        return $breakdown;
    }

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
         * @param  string  $search_id
         * @param  string  $type
         * @param  string  $search_type
         * @param  string  $suppliers
         * @param  array  $filters
         */
        [$search_id, $filters, $suppliers, $type, $search_type] = $input;

        $token_id = ChannelRepository::getTokenId($filters['token_id']);

        $inspector = new ApiSearchInspector();
        $inspector->search_id = $search_id;
        $inspector->type = $type;
        $inspector->search_type = $search_type;
        $inspector->suppliers = implode(',', $suppliers);
        $inspector->request = json_encode($filters);
        $inspector->token_id = $token_id;
        $inspector->destination_name = Arr::get($filters, 'destination_name');

        \Log::info('Created ApiSearchInspector:', ['inspector' => $inspector]);

        return $inspector->toArray();
    }
}
