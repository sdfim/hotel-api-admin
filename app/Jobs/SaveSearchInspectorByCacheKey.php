<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Inspector\SearchInspectorController;

class SaveSearchInspectorByCacheKey implements ShouldQueue
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
        private readonly array $cacheKeys,
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

        /** @var SearchInspectorController $searchInspector */
        $searchInspector = app(SearchInspectorController::class);
        $searchInspector->save([$this->inspector, ['keyCache' => $this->cacheKeys], [], []]);
    }
}
