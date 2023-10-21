<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaTools;
use Modules\Inspector\BookingInspectorController;

class SaveBookingInspector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $expediaTools;
	private $bookingInspector;
	private $dataQueue;
    /**
     * Create a new job instance.
     */
    public function __construct($dataQueue)
    {
		$this->expediaTools = new ExpediaTools();
		$this->bookingInspector = new BookingInspectorController();
		$this->dataQueue = $dataQueue;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
		[$booking_id, $query, $content, $client_content, $supplier_id, $type, $subType, $search_type] = $this->dataQueue;

		$this->bookingInspector->save($booking_id, $query, $content, $client_content, $supplier_id, $type, $subType, $search_type);
		
		if ($type == 'add_item' && $subType == 'retrieve') $this->expediaTools->saveAddItemToReservations($booking_id, $query);
    }
}
