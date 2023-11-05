<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Inspector\SearchInspectorController;

class SaveSearchInspector implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var SearchInspectorController
     */
    private SearchInspectorController $searchInspector;

    /**
     * @var array
     */
    private array $dataQueue;

    /**
     * Create a new job instance.
     */
    public function __construct($dataQueue)
    {
        $this->searchInspector = new SearchInspectorController();
        $this->dataQueue = $dataQueue;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        [$search_id, $filters, $content, $clientContent, $supplierIds, $type, $search_type] = $this->dataQueue;

		try {
			$this->searchInspector->save($search_id, $filters, $content, $clientContent, $supplierIds, $type, $search_type);
		} catch (\Exception $e) {
			\Log::error('SaveSearchInspector: ' . $e->getMessage());
		}

    }
}
