<?php

namespace Modules\Inspector;

class BaseInspectorController
{
    /**
     * @var string|float
     */
    protected string|float $current_time;

    /**
     * @return string|float
     */
    public function executionTime(): string|float
    {
        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }
}
