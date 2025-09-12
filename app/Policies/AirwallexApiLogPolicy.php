<?php

namespace App\Policies;

use App\Models\AirwallexApiLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirwallexApiLogPolicy
{
    protected static string $prefix = 'airwallex_api_log';
}
