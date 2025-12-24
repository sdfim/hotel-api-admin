<?php

namespace App\Observers;

use App\Models\GeneralConfiguration;
use Illuminate\Support\Facades\Cache;

class GeneralConfigurationObserver
{
    public function created(GeneralConfiguration $generalConfiguration)
    {
        Cache::put('general_configuration', $generalConfiguration->toArray());
    }

    public function updated(GeneralConfiguration $generalConfiguration)
    {
        Cache::put('general_configuration', $generalConfiguration->toArray());
    }
}
