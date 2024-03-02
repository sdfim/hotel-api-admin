<?php

namespace App\Repositories;

use App\Models\GeneralConfiguration;

class ConfigRepository
{
    public static function getTimeout(): int
    {
        return GeneralConfiguration::first()->time_supplier_requests;
    }
}
