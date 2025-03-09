<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\API\Tools\ClearSearchCacheByBookingItemsTools;

class ClearSearchCacheByBookingItemsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly array $bookingItems,
    ) {}

    public function handle(): void
    {
        /** @var ClearSearchCacheByBookingItemsTools $searchCache */
        $searchCache = app(ClearSearchCacheByBookingItemsTools::class);
        $searchCache->clear($this->bookingItems);
    }
}
