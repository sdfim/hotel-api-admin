<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class ProductPolicy extends BasePolicy
{
    protected static bool $withTeam = true;
    protected static string $prefix = 'product';
}
