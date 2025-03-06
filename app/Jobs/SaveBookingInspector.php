<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Modules\Inspector\BookingInspectorController;

class SaveBookingInspector implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $inspector,
        private readonly array $content = [],
        private readonly array $client_content = [],
        private readonly string $status = 'success',
        private readonly array $status_describe = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->inspector['status'] = $this->status;
        $this->inspector['status_describe'] = $this->status_describe;
        $this->inspector['type'] = Arr::get($this->inspector, 'type', 'hotel');

        /* @var BookingInspectorController $bookingInspector */
        $bookingInspector = app(BookingInspectorController::class);

        $bookingInspector->save(
            $this->inspector,
            $this->content,
            $this->client_content
        );
    }
}
