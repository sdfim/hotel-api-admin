<?php

namespace App\Repositories;

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
            if (isset($result['mapper_hbsi_giata']) && $result['mapper_hbsi_giata'] != null) {
                $result['giata_id'] = $result['mapper_hbsi_giata'][0]['giata_id'];
                $result['perc'] = $result['mapper_hbsi_giata'][0]['perc'];
            }
        }
        return ['results' => $results, 'count' => count($results)];
    }

}
