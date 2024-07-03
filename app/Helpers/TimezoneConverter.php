<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimezoneConverter
{
    public static function convertUtcToEst($dateTime)
    {
        return Carbon::parse($dateTime)->timezone('America/New_York')->toDateTimeString();
    }

    public static function convertEstToUtc($dateTime)
    {
        return Carbon::parse($dateTime, 'America/New_York')->timezone('UTC')->toDateTimeString();
    }
}
