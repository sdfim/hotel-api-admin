<?php

namespace App\Jobs;

use App\Models\ApiBookingsMetadata;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class SaveBookingMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $filters,
        private readonly array $reservation
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ApiBookingsMetadata::insert([
            'booking_item'              => Arr::get($this->filters, 'booking_item'),
            'booking_id'                => Arr::get($this->filters, 'booking_id'),
            'supplier_id'               => Arr::get($this->filters, 'supplier_id'),
            'supplier_booking_item_id'  => Arr::get($this->reservation, 'bookingId'),
            'booking_item_data'         => json_encode($this->reservation),
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
        ]);
    }
}
