<?php

namespace App\Support\Services\Logging\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LogUuidProcessor implements ProcessorInterface
{
    public function __construct(protected string $key = 'extra.log_uuid') {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        return data_set($record, $this->key, request()->get('uuid'));
    }
}
