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
use Modules\Enums\TypeRequestEnum;

class ClearSearchCacheByBookingItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $bookingItems
    ){}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);
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
                    $search_ids_data = ApiSearchInspector::whereIn('search_id', function ($query) use ($hotel_id, $room_id) {
                        $query->select('search_id')
                            ->from('api_booking_items')
                            ->whereJsonContains('booking_item_data->hotel_id', $hotel_id)
                            ->where('booking_item_data->room_id', 'like', $room_id)
                            ->where('created_at', '>', Carbon::now()->subHour());
                    })
                        ->join('channels', 'api_search_inspector.token_id', '=', 'channels.token_id')
                        ->get(['request', 'api_search_inspector.token_id', 'channels.access_token'])
                        ->toArray();

                    // Process each search data item
                    foreach ($search_ids_data as $search_id_data) {
                        $filters = json_decode($search_id_data['request'], true);
                        $token = explode('|', $search_id_data['access_token'])[1];
                        $currentKeyPricingSearch = TypeRequestEnum::HOTEL->value . ':pricingSearch:' . http_build_query(Arr::dot($filters)) . ':' . $token;

                        // Clear cache by key
                        $tag = 'pricing_search';
                        $taggedCache = Cache::tags($tag);
                        if ($taggedCache->has($currentKeyPricingSearch . ':result')) {
                            Cache::forget($currentKeyPricingSearch);
                            Log::debug('ClearSearchCacheByBookingItemsJob | Cache removed: ' . $currentKeyPricingSearch);
                        }
                        else {
                            Log::debug('ClearSearchCacheByBookingItemsJob | Cache not found: ' . $currentKeyPricingSearch);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in ClearSearchCacheByBookingItemsJob: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            $this->fail($e);
        }
        $end = microtime(true);
        $execution_time = $end - $start;

//        Log::info('ClearSearchCacheByBookingItemsJob | Execution time of handle() in seconds: ' . $execution_time);
    }
}
