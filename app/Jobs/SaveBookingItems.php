<?php

namespace App\Jobs;

use App\Models\ApiBookingItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveBookingItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @var array
	 */
	private array $bookingItems;

	public $tries = 5;
	public $retryAfter = 250;
	
    /**
     * Create a new job instance.
     */
    public function __construct(array $bookingItems)
    {
        $this->bookingItems = $bookingItems;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ApiBookingItem::insert($this->bookingItems);
    }
}
