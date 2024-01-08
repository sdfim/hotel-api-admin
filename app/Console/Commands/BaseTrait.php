<?php

namespace App\Console\Commands;

trait BaseTrait
{

    protected array $current_time;

    /**
     * @param string $type
     * @return float
     */
    private function executionTime(string $type): float
    {
        $execution_time = round((microtime(true) - $this->current_time[$type]), 3);
        $this->current_time[$type] = microtime(true);

        return $execution_time;
    }
}
