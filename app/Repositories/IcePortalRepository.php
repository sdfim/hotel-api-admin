<?php

namespace App\Repositories;

use App\Models\GiataProperty;
use App\Models\IcePortalPropery;

class IcePortalRepository
{
    public function __construct()
    {
    }

    /**
     * @param string $city
     * @return array
     */
    public static function dataByCity(string $city): array
    {
        $results = IcePortalPropery::where('city', $city)->with('mapperHbsiGiata')->get()->toArray();
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

        return $query->leftJoin('mapper_ice_portal_giatas', 'mapper_ice_portal_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_ice_portal_giatas.ice_portal_id')
            ->whereNotNull('mapper_ice_portal_giatas.ice_portal_id')
            ->get()
            ->pluck('ice_portal_id')
            ->toArray();
    }

    public static function getIdByCoordinate(array $minMaxCoordinate): int
    {
        return GiataProperty::where('giata_properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('giata_properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('giata_properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('giata_properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin('mapper_ice_portal_giatas', 'mapper_ice_portal_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_ice_portal_giatas.ice_portal_id')
            ->whereNotNull('mapper_ice_portal_giatas.ice_portal_id')
            ->first()
            ->ice_portal_id ?? 0;
    }

}
