<?php

namespace App\Policies\Configurations;

use App\Policies\Base\BasePolicy;

class ConfigAttributePolicy extends BasePolicy
{
    protected static string $prefix = 'config_attribute';
}
