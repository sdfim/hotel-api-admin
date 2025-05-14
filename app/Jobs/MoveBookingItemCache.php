<?php

namespace App\Jobs;

use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MoveBookingItemCache implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $bookingItem
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bookingItemCache = ApiBookingItemCache::where('booking_item', $this->bookingItem)->first();

        \Log::debug('MoveBookingItemCache', ['booking_item' => $this->bookingItem, 'bookingItemCache' => $bookingItemCache]);

        DB::transaction(function () use ($bookingItemCache) {
            $bookingItem = $bookingItemCache->toArray();
            $bookingItem['child_items'] = json_encode($bookingItem['child_items']);
            ApiBookingItem::insert($bookingItem);
            $bookingItemCache->delete();
        });
    }
}
