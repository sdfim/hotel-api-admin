<?php

namespace App\Jobs;

use App\Models\ApiBookingItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveBookingItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $bookingItems
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ApiBookingItem::insert($this->bookingItems);
    }
}
