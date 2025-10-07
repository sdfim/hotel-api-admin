<?php

namespace App\Enums;

use Illuminate\Support\Traits\EnumeratesValues;

enum BookingStatusEnum: string
{
    case BOOKED = 'booked';
    case CANCELED = 'canceled';
    case MODIFIED = 'modified';
    // Add more statuses as needed
}

