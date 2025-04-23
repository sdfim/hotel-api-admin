<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class HotelRoomPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'hotel_room';
}
