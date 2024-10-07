<?php

namespace Modules\API\Tools;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;

class ClearSearchCacheByBookingItemsTools {

    /**
     * @param array $bookingItems
     * @return void
     */
    public function clear(array $bookingItems): void
    {
        $start = microtime(true);

        $tag = 'pricing_search';
        $taggedCache = Cache::tags($tag);

        $arr_pricing_search = $taggedCache->get('arr_pricing_search') ?? []; // local dev none cache
        Log::debug('ClearSearchCacheByBookingItemsTools | befor $arr_pricing_search : ' . json_encode($arr_pricing_search));

        try {
            $stbi = microtime(true);
            $bookingItemsData = ApiBookingItem::whereIn('booking_item', $bookingItems)->get()->keyBy('booking_item');

            Log::debug('ClearSearchCacheByBookingItemsTools | count $bookingItemsData : ' . count($bookingItemsData)
                . ' | runtime search: ' . (microtime(true) - $stbi) . ' seconds');

            foreach ($bookingItems as $bookingItem) {
                $stbi = microtime(true);
                // Get booking item data
                $booking_item_data = $bookingItemsData[$bookingItem]->booking_item_data;
                $booking_item_data = json_decode($booking_item_data, true);
                $room_ids_str = Arr::get($booking_item_data, 'room_id');
                $hotel_id = Arr::get($booking_item_data, 'hotel_id');
                $room_ids = explode(';', $room_ids_str);

                $search_ids = ApiBookingItem::whereIn('search_id', $arr_pricing_search)
                    ->where('hotel_id', $hotel_id)
                    ->whereIn('room_id', $room_ids)
                    ->where('created_at', '>', Carbon::now()->subMinutes(60))
                    ->pluck('search_id')
                    ->toArray();

                // Get search data for the retrieved search IDs
                $search_ids_data = ApiSearchInspector::whereIn('search_id', $search_ids)
                    ->join('channels', 'api_search_inspector.token_id', '=', 'channels.token_id')
                    ->get(['search_id', 'request', 'api_search_inspector.token_id', 'channels.access_token'])
                    ->toArray();

                Log::debug('ClearSearchCacheByBookingItemsTools | count $search_ids_data : ' . count($search_ids_data) . ' | search runtime: '
                    . (microtime(true) - $stbi) . ' seconds', [$search_ids_data]);

                // Process each search data item
                foreach ($search_ids_data as $search_id_data) {
                    if (!in_array($search_id_data['search_id'], $arr_pricing_search)) {
                        Log::debug('ClearSearchCacheByBookingItemsTools | Cache not found: ' . $search_id_data['search_id']);
                        continue;
                    }

                    $currentKeyPricingSearch = $taggedCache->get($search_id_data['search_id']);

                    $taggedCache->forget($search_id_data['search_id']);
                    $taggedCache->forget($currentKeyPricingSearch);

                    $key = array_search($search_id_data['search_id'], $arr_pricing_search);
                    if ($key !== false) unset($arr_pricing_search[$key]);
                    $taggedCache->put('arr_pricing_search', $arr_pricing_search, now()->addMinutes(HotelApiHandler::TTL));

                    Log::debug('ClearSearchCacheByBookingItemsTools | Cache removed: ' . $search_id_data['search_id'],
                        [
                            'currentKeyPricingSearch' => $currentKeyPricingSearch,
                            'arr_pricing_search' => $arr_pricing_search
                        ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in ClearSearchCacheByBookingItemsTools: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
        }

        Log::debug('ClearSearchCacheByBookingItemsTools | after $arr_pricing_search : ' . json_encode($arr_pricing_search));
        Log::info('ClearSearchCacheByBookingItemsTools | Execution time of clear() in seconds: ' . microtime(true) - $start . ' seconds');
    }
}
