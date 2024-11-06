<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class HotelImagePolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'hotel_image';
}
