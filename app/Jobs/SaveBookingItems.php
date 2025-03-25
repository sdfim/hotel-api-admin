<?php

namespace App\Jobs;

use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;

class SaveBookingItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $bookingItems,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $chunks = array_chunk($this->bookingItems, 100);

        foreach ($chunks as $k => $value) {
            try {
                foreach ($value as $item) {
                    $cache_checkpoint = Arr::get($item, 'cache_checkpoint', null);
                    $search_id = Arr::get($item, 'search_id', null);
                    if ($cache_checkpoint) {
                        $keyCache = 'searched:'.$cache_checkpoint;
                        Cache::put($keyCache, $search_id, HotelApiHandler::TTL);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('SaveBookingItems', ['error' => $e->getMessage()]);
            }
            ApiBookingItemCache::insert($value);
        }
    }
}
