<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class PermissionPolicy extends BasePolicy
{
    protected static string $prefix = 'permission';
}
