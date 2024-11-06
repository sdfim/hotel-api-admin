<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class InsuranceProviderPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'insurance_provider';
}
