<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Inspector\SearchInspectorController;
use Illuminate\Support\Facades\Log;

class SaveSearchInspector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly array                     $dataQueue,
        private readonly SearchInspectorController $searchInspector = new SearchInspectorController(),
    )
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->searchInspector->save($this->dataQueue);
    }
}
