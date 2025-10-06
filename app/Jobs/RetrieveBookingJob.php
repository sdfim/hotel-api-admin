<?php

namespace App\Jobs;

use App\Repositories\ApiBookingsMetadataRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Requests\BookingRetrieveItemsRequest;

class RetrieveBookingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $bookingId,
    ) {}

    public function handle(): void
    {
        sleep(1);

        $timeout = 10;
        $elapsed = 0;
        do {
            $itemsBooked = ApiBookingsMetadataRepository::bookedItems($this->bookingId);
            if (! empty($itemsBooked) && count($itemsBooked) > 0) {
                logger()->info('Items found for booking', ['booking_id' => $this->bookingId, 'items_count' => count($itemsBooked)]);
                break;
            }
            sleep(1);
            $elapsed++;
        } while ($elapsed < $timeout);

        /** @var BookApiHandler $bookApiHandler */
        $bookApiHandler = app(BookApiHandler::class);
        $bookApiHandler->retrieveBooking(new BookingRetrieveItemsRequest(['booking_id' => $this->bookingId]));
        logger()->info('RetrieveBookingJob completed', ['booking_id' => $this->bookingId]);
    }
}
