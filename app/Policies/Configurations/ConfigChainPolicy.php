<?php

namespace App\Policies\Configurations;

use App\Policies\Base\BasePolicy;

class ConfigChainPolicy extends BasePolicy
{
    protected static string $prefix = 'config_chain';
}
