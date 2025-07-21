<?php

namespace App\Support\Services\Logging\Processors;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogRequestDataProcessor implements ProcessorInterface
{
    public function __construct() {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $key = 'extra.request_body';
        $content = json_decode(request()->getContent(), true);

        if (request()->method() === 'GET') {
            $content = ! empty(request()->query()) ? request()->query() : null;
            $key = 'extra.request_params';
        }

        return data_set($record, $key, $content);
    }
}
