<?php

namespace App\Policies;

use App\Models\User;

class ChannelPolicy extends BasePolicy
{
    protected static string $prefix = 'channel';
}
