<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaTools;

class SaveReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @param string $booking_id
     * @param array $filters
     * @param array $dataPassengers
     * @param ExpediaTools $expediaTools
     */
    public function __construct(
        private readonly string   $booking_id,
        private readonly array $filters,
        private readonly array $dataPassengers,
        private readonly ExpediaTools $expediaTools = new ExpediaTools(),
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->expediaTools->saveAddItemToReservations($this->booking_id, $this->filters, $this->dataPassengers);
    }
}
