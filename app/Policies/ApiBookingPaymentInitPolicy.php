<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class ApiBookingPaymentInitPolicy extends BasePolicy
{
    protected static string $prefix = 'api_booking_payment_init';
}

