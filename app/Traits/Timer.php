<?php

namespace App\Traits;

trait Timer
{
    /**
     * @var array
     */
    private array $start;

    /**
     * @param string $key
     * @return void
     */
    public function start(string $key = 'main'): void
    {
        $this->start[$key] = microtime(true);
    }

    /**
     * @param string $key
     * @return float
     */
    public function duration(string $key = 'main'): float
    {
        $execution_time = microtime(true) - $this->start[$key];
        $this->start[$key] = microtime(true);

        return $execution_time;
    }
}
