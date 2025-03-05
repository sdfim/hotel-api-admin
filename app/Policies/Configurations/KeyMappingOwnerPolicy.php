<?php

namespace App\Policies\Configurations;

use App\Policies\Base\BasePolicy;

class KeyMappingOwnerPolicy extends BasePolicy
{
    protected static string $prefix = 'config_external_identifier';
}

