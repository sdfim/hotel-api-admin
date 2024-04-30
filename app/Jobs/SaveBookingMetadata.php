<?php

namespace App\Jobs;

use App\Models\ApiBookingsMetadata;
use App\Repositories\ApiBookingItemRepository;
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
        $bookingItem = Arr::get($this->filters, 'booking_item');
        $hotelId = null;

        if ($bookingItem !== null) {
            $hotelId = ApiBookingItemRepository::getHotelSupplierId($bookingItem);
        }

        ApiBookingsMetadata::insert([
            'booking_item'              => $bookingItem,
            'booking_id'                => Arr::get($this->filters, 'booking_id'),
            'supplier_id'               => Arr::get($this->filters, 'supplier_id'),
            'supplier_booking_item_id'  => Arr::get($this->reservation, 'bookingId'),
            'hotel_supplier_id'         => $hotelId,
            'booking_item_data'         => json_encode($this->reservation),
            'created_at'                => Carbon::now(),
            'updated_at'                => Carbon::now(),
        ]);
    }
}
