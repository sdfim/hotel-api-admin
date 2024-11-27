<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class VendorPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'vendor';
}
