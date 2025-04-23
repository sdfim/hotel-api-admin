<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;
use App\Policies\Base\ByCurrentTeam;

class ProductPolicy extends BasePolicy
{
    use ByCurrentTeam;

    protected static bool $withTeam = true;
    protected static string $prefix = 'product';
}
