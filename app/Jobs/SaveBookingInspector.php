<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Inspector\BookingInspectorController;

class SaveBookingInspector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array $dataQueue,
        private readonly BookingInspectorController $bookingInspector = new BookingInspectorController(),
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->bookingInspector->save($this->dataQueue);
    }
}
