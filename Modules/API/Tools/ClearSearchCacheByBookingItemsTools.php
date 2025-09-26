<?php

namespace Modules\API\Tools;

use App\Models\ApiBookingItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClearSearchCacheByBookingItemsTools
{
    public function clear(array $bookingItems): void
    {
        $start = microtime(true);

        $tag = 'pricing_search';
        $taggedCache = Cache::tags($tag);

        try {
            $stbi = microtime(true);
            $bookingItemsData = ApiBookingItem::whereIn('booking_item', $bookingItems)->get()->keyBy('booking_item');

            Log::debug('ClearSearchCacheByBookingItemsTools | count $bookingItemsData : '.count($bookingItemsData)
                .' | runtime search: '.(microtime(true) - $stbi).' seconds');

            foreach ($bookingItems as $bookingItem) {
                $stbi = microtime(true);
                // Get booking item data
                $booking_item_data = $bookingItemsData[$bookingItem]->booking_item_data;
                $booking_item_data = json_decode($booking_item_data, true);

                // Hotel type booking
                $room_ids_str = Arr::get($booking_item_data, 'room_id') ?? Arr::get($booking_item_data, 'room_code');
                $rate_code_str = Arr::get($booking_item_data, 'rate_code') ?? Arr::get($booking_item_data, 'rate');
                $hotel_id = Arr::get($booking_item_data, 'hotel_id');
                $supplier = Arr::get($booking_item_data, 'supplier');
                if ($hotel_id !== null && $room_ids_str !== null) {
                    $room_ids = explode(';', $room_ids_str);
                    $rate_codes = explode(';', $rate_code_str);
                    foreach ($room_ids as $k => $room_id) {
                        $keyCache = 'searched:'.$hotel_id.':'.$room_ids[$k].':'.$rate_codes[$k].':'.$supplier;
                        $search_id = Cache::get($keyCache);
                        if ($search_id) {
                            $taggedCache->forget($search_id);
                        }
                    }
                }
                \Log::debug('ClearSearchCacheByBookingItemsTools | runtime search: '.(microtime(true) - $stbi).' seconds');
            }
        } catch (\Exception $e) {
            Log::error('Error in ClearSearchCacheByBookingItemsTools: '.$e->getMessage());
            Log::error('Trace: '.$e->getTraceAsString());
        }

        Log::info('ClearSearchCacheByBookingItemsTools | Execution time of clear() in seconds: '.microtime(true) - $start.' seconds');
    }
}
