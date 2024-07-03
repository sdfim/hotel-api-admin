<?php

namespace App\Repositories;

use App\Models\GiataProperty;
use Illuminate\Support\Facades\DB;

class IcePortalRepository
{
    public function __construct()
    {
    }

    public static function dataByCity(string $city): array
    {
        $mainDB = config('database.connections.mysql.database');
        $cacheDB = config('database.connections.mysql_cache.database');

        $results = DB::connection('mysql_cache')
            ->table("$cacheDB.ice_hbsi_properties")
            ->leftJoin("$mainDB.mapper_ice_portal_giatas", "$cacheDB.ice_hbsi_properties.code", '=', "{$mainDB}.mapper_ice_portal_giatas.ice_portal_id")
            ->where('city', $city)
            ->select("$cacheDB.ice_hbsi_properties.*", "$mainDB.mapper_ice_portal_giatas.*")
            ->get()
            ->map(function ($value) {
                return (array) $value;
            })
            ->toArray();

        foreach ($results as &$result) {
            if (isset($result['mapper_ice_portal_giatas']) && $result['mapper_ice_portal_giatas'] != null) {
                $result['giata_id'] = $result['mapper_ice_portal_giatas'][0]['giata_id'];
                $result['perc'] = $result['mapper_ice_portal_giatas'][0]['perc'];
            }
        }

        return ['results' => $results, 'count' => count($results)];
    }

    public static function getIdsByDestinationGiata(string $input): array
    {
        if (is_numeric($input)) {
            $query = GiataProperty::where('city_id', $input);
        } else {
            $query = GiataProperty::where('city', $input);
        }

        $mainDB = config('database.connections.mysql.database');

        return $query->leftJoin($mainDB.'.mapper_ice_portal_giatas', $mainDB.'.mapper_ice_portal_giatas.giata_id', '=', 'giata_properties.code')
            ->select($mainDB.'.mapper_ice_portal_giatas.ice_portal_id')
            ->whereNotNull($mainDB.'.mapper_ice_portal_giatas.ice_portal_id')
            ->get()
            ->pluck('ice_portal_id')
            ->toArray();
    }

    public static function getIdByCoordinate(array $minMaxCoordinate): int
    {
        $mainDB = config('database.connections.mysql.database');

        return GiataProperty::where('giata_properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('giata_properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('giata_properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('giata_properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin($mainDB.'.mapper_ice_portal_giatas', $mainDB.'.mapper_ice_portal_giatas.giata_id', '=', 'giata_properties.code')
            ->select($mainDB.'.mapper_ice_portal_giatas.ice_portal_id')
            ->whereNotNull($mainDB.'.mapper_ice_portal_giatas.ice_portal_id')
            ->first()
            ->ice_portal_id ?? 0;
    }
}
