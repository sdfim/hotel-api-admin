<?php

namespace App\Support\Services\Logging\Processors;

use Illuminate\Support\Facades\App;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LogCommandInformationProcessor implements ProcessorInterface
{
    public function __construct() {}

    public function __invoke(LogRecord $record): LogRecord
    {
        if (App::runningInConsole()) {
            $command = $this->getCurrentCommand();
            $record['extra']['command'] = $command;
        }

        return $record;
    }

    protected function getCurrentCommand(): string
    {
        $command = implode(' ', array_slice($_SERVER['argv'], 1));

        return ! empty($command) ? $command : 'Unknown Command';
    }
}
