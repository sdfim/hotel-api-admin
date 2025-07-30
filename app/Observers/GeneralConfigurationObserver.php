<?php

namespace App\Observers;

use App\Models\GeneralConfiguration;
use Illuminate\Support\Facades\Cache;

class GeneralConfigurationObserver
{
    /**
     * Handle the GeneralConfiguration "created" event.
     */
    public function created(GeneralConfiguration $generalConfiguration)
    {
        $this->cacheContentSupplier($generalConfiguration);
    }

    /**
     * Handle the GeneralConfiguration "updated" event.
     */
    public function updated(GeneralConfiguration $generalConfiguration)
    {
        $this->cacheContentSupplier($generalConfiguration);
    }

    /**
     * Handle the GeneralConfiguration "deleted" event.
     */
    public function deleted(GeneralConfiguration $generalConfiguration)
    {
        // ...
    }

    /**
     * Handle the GeneralConfiguration "restored" event.
     */
    public function restored(GeneralConfiguration $generalConfiguration)
    {
        // ...
    }

    /**
     * Handle the GeneralConfiguration "force deleted" event.
     */
    public function forceDeleted(GeneralConfiguration $generalConfiguration)
    {
        // ...
    }

    protected function cacheContentSupplier(GeneralConfiguration $generalConfiguration)
    {
        if (! is_null($generalConfiguration->content_supplier)) {
            Cache::forever('constant:content_supplier', $generalConfiguration->content_supplier);
        }
    }
}
