<?php

namespace Modules\API\Tools;

use Illuminate\Support\Facades\Log;

class MemoryLogger
{
    public static function log(string $context): void
    {
        $memory = round(memory_get_usage(true) / 1024 / 1024, 1);
        $peak = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
        Log::info("Memory usage in {$context}: {$memory}MB (Peak: {$peak}MB)");
    }
}
