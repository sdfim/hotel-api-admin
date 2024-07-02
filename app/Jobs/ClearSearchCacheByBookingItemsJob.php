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
use Modules\API\Tools\ClearSearchCacheByBookingItemsTools;

class ClearSearchCacheByBookingItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @param array $bookingItems
     * @param ClearSearchCacheByBookingItemsTools $searchCache
     */
    public function __construct(
        private readonly array $bookingItems,
        private readonly ClearSearchCacheByBookingItemsTools $searchCache = new ClearSearchCacheByBookingItemsTools(),
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->searchCache->clear($this->bookingItems);
    }
}
