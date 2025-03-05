<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class HotelRatePolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'hotel_rate';
}
