<?php

namespace App\Support\Services\Logging\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogIpProcessor implements ProcessorInterface
{
    public function __construct(protected string $key = 'extra.ip')
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        return data_set($record, $this->key, request()->ip());
    }
}
