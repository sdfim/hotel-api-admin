<?php

namespace Modules\Inspector;

class BaseInspectorController
{
	protected $current_time;
	public function executionTime ()
    {
        $execution_time = (microtime(true) - $this->current_time);
        $this->current_time = microtime(true);

        return $execution_time;
    }

}