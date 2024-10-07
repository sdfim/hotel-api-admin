<?php

namespace App\Support\Services\Logging\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogRequestUrlProcessor implements ProcessorInterface
{
    public function __construct(protected string $key = 'extra.request_url')
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        return data_set($record, $this->key, request()->fullUrl());
    }
}
