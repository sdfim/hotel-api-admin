<?php

namespace App\Repositories;

use App\Models\GiataGeography;
use Illuminate\Support\Facades\DB;

class GiataGeographyRepository
{
    public function getFullLocation(int $cityId): GiataGeography
    {
        return GiataGeography::where('city_id', $cityId)
            ->select([DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location")])
            ->first();
    }
}
