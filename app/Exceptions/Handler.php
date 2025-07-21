<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    private function requestInformation(): string
    {
        return json_encode(request()->all());
    }
    public function report(Throwable $e)
    {
        Log::error($e->getMessage().PHP_EOL.$this->requestInformation(), $e->getTrace());
        parent::report($e);
    }
}
