<?php

namespace Modules\Inspector;

class BaseInspectorController
{
    protected string|float $current_time;

    protected const PATH_INSPECTORS = 'inspectors/';

    public function executionTime(): string|float
    {
        $execution_time = (microtime(true) - ($this->current_time ?? microtime(true)));
        $this->current_time = microtime(true);

        return $execution_time;
    }
}
