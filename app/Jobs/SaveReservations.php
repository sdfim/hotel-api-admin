<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaTools;

class SaveReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $booking_id;
	private $filters;
	private $dataPassengers;

    /**
     * Create a new job instance.
     */
    public function __construct($booking_id, $filters, $dataPassengers)
    {
        $this->booking_id = $booking_id;
		$this->filters = $filters;
		$this->dataPassengers = $dataPassengers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expediaTools = new ExpediaTools();
		$expediaTools->saveAddItemToReservations($this->booking_id, $this->filters, $this->dataPassengers);
    }
}
