<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class InsuranceRestrictionPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'insurance_restriction';
}
