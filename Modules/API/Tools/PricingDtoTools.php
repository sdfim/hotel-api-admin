<?php

namespace Modules\API\Tools;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\GiataProperty;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PricingDtoTools {

    public function getGiataProperties(array $query, array $giataIds): array
    {
        $latitude = Arr::get($query, 'latitude', 0);
        $longitude = Arr::get($query, 'longitude', 0);

        if ($latitude == 0 && $longitude == 0) {
            return GiataProperty::whereIn('code', $giataIds)
                ->select('code', 'city')
                ->get()
                ->keyBy('code')
                ->map(function($item) {
                    return [
                        'city' => $item->city,
                    ];
                })
                ->toArray();
        } else {
            return GiataProperty::whereIn('code', $giataIds)
                ->selectRaw('code, city, 6371 * 2 * ASIN(SQRT(POWER(SIN((latitude - abs(?)) * pi()/180 / 2), 2) + COS(latitude * pi()/180 ) * COS(abs(?) * pi()/180) * POWER(SIN((longitude - ?) *  pi()/180 / 2), 2))) as distance', [$latitude, $latitude, $longitude])
                ->get()
                ->keyBy('code')
                ->map(function($item) {
                    return [
                        'city' => $item->city,
                        'distance' => $item->distance,
                    ];
                })
                ->toArray();
        }
    }

    public function getDestinationData(array $query): string|null
    {
        if (isset($query['destination'])) {
            $destinationData = GiataGeography::where('city_id', $query['destination'])
                ->select([
                    DB::raw("CONCAT(city_name, ', ', locale_name, ', ', country_name) as full_location"),
                ])
                ->first()->full_location ?? '';
        } elseif (isset($query['place'])) {
            $destinationData = GiataPlace::where('key', $query['place'])
                ->select([
                    DB::raw("CONCAT(name_primary, ', ', type, ', ', country_code) as full_location"),
                ])
                ->first()->full_location ?? '';
        } else {
            $destinationData = null;
        }

        return $destinationData;
    }
}
