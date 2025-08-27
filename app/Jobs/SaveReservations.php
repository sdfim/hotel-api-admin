<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\API\Tools\ReservationTools;

class SaveReservations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $booking_id,
        private readonly array $filters,
        private readonly array $dataPassengers,
        private readonly string $token,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /* @var ReservationTools $reservationTools */
        $reservationTools = app(ReservationTools::class);
        $reservationTools->saveAddItemToReservations($this->booking_id, $this->filters, $this->dataPassengers, $this->token);
    }
}
