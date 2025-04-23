<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class ApiBookingItemPolicy extends BasePolicy
{
    protected static string $prefix = 'api_booking_item';
}
