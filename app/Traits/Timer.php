<?php

namespace App\Traits;

trait Timer
{
    private array $start;

    public function start(string $key = 'main'): void
    {
        $this->start[$key] = microtime(true);
    }

    public function duration(string $key = 'main'): float
    {
        $execution_time = microtime(true) - $this->start[$key];
        $this->start[$key] = microtime(true);

        return $execution_time;
    }
}
