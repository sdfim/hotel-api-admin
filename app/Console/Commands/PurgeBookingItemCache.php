<?php

namespace App\Console\Commands;

use App\Models\ApiBookingItemCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeBookingItemCache extends Command
{
    protected $signature = 'purge-booking-item-cache';

    protected $description = 'Purge ApiBookingItemCache entries older than 1 hour';

    public function handle(): void
    {
        $this->info('PurgeBookingItemCache: Deleting entries older than 1 hour');
        $oneHourAgo = now()->subHour();

        $bookingItemCaches = ApiBookingItemCache::where('created_at', '<', $oneHourAgo);
        $count = $bookingItemCaches->count();

        if ($count > 0) {
            $bookingItemCaches->delete();
        }

        $this->info("PurgeBookingItemCache: Deleted $count entries older than 1 hour");
    }
}
