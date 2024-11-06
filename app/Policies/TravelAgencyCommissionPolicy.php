<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class TravelAgencyCommissionPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'travel_agency_commission';
}
