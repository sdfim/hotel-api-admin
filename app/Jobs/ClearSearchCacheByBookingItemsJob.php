<?php

namespace App\Jobs;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;

class ClearSearchCacheByBookingItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $bookingItems
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);

        $tag = 'pricing_search';
        $taggedCache = Cache::tags($tag);

        $arr_pricing_search = $taggedCache->get('arr_pricing_search') ?? []; // local dev none cache
        //        Log::debug('ClearSearchCacheByBookingItemsJob | $arr_pricing_search : ' . json_encode($arr_pricing_search));

        try {
            // Process each booking item
            foreach ($this->bookingItems as $bookingItem) {
                // Get booking item data
                $booking_item_data = ApiBookingItem::where('booking_item', $bookingItem)->first()->booking_item_data;
                $booking_item_data = json_decode($booking_item_data, true);
                $room_ids_str = Arr::get($booking_item_data, 'room_id');
                $hotel_id = Arr::get($booking_item_data, 'hotel_id');
                $room_ids = explode(';', $room_ids_str);

                // Process each room ID
                foreach ($room_ids as $room_id) {
                    // Get search data by hotel ID and room ID
                    $search_ids_data = ApiSearchInspector::whereIn('search_id', function ($query) use ($hotel_id, $room_id, $arr_pricing_search) {
                        $query->select('search_id')
                            ->from('api_booking_items')
                            ->whereIn('search_id', $arr_pricing_search)
                            ->whereJsonContains('booking_item_data->hotel_id', $hotel_id)
                            ->where('booking_item_data->room_id', 'like', $room_id)
                            ->where('created_at', '>', Carbon::now()->subHour());
                    })
                        ->join('channels', 'api_search_inspector.token_id', '=', 'channels.token_id')
                        ->get(['search_id', 'request', 'api_search_inspector.token_id', 'channels.access_token'])
                        ->toArray();

                    //                    Log::debug('ClearSearchCacheByBookingItemsJob | $search_ids_data : ' . json_encode($search_ids_data));

                    // Process each search data item
                    foreach ($search_ids_data as $search_id_data) {

                        if (! in_array($search_id_data['search_id'], $arr_pricing_search)) {
                            Log::debug('ClearSearchCacheByBookingItemsJob | Cache not found: '.$search_id_data['search_id']);

                            continue;
                        }

                        $currentKeyPricingSearch = $taggedCache->get($search_id_data['search_id']);

                        $taggedCache->forget($search_id_data['search_id']);
                        $taggedCache->forget($currentKeyPricingSearch);

                        $key = array_search($search_id_data['search_id'], $arr_pricing_search);
                        if ($key !== false) {
                            unset($arr_pricing_search[$key]);
                        }
                        $taggedCache->put('arr_pricing_search', $arr_pricing_search, now()->addMinutes(HotelApiHandler::TTL));

                        Log::debug('ClearSearchCacheByBookingItemsJob | Cache removed: '.$search_id_data['search_id'],
                            [
                                'currentKeyPricingSearch' => $currentKeyPricingSearch,
                                'arr_pricing_search' => $arr_pricing_search,
                            ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in ClearSearchCacheByBookingItemsJob: '.$e->getMessage());
            Log::error('Trace: '.$e->getTraceAsString());
            $this->fail($e);
        }
        $end = microtime(true);
        $execution_time = $end - $start;

        Log::info('ClearSearchCacheByBookingItemsJob | Execution time of handle() in seconds: '.$execution_time);
    }
}
