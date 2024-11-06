<?php

namespace App\Policies;

use App\Policies\Base\BasePolicy;

class UserPolicy extends BasePolicy
{
    protected static string $prefix = 'user';
}
