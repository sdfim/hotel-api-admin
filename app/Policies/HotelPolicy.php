<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;
use App\Policies\Base\ByCurrentTeam;

class HotelPolicy extends BasePolicy
{
    use ByCurrentTeam;

    protected ?string $withRelation = 'product';

    protected static bool $withTeam = true;
    protected static string $prefix = 'hotel';
}
