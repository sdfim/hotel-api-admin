<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class ApiBookingInspectorPolicy extends BasePolicy
{
    protected static string $prefix = 'api_booking_inspector';
}
