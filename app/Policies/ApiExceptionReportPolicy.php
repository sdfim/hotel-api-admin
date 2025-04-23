<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class ApiExceptionReportPolicy extends BasePolicy
{
    protected static string $prefix = 'api_exception_report';
}
