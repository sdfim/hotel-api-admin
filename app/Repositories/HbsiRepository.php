<?php

namespace App\Repositories;

use App\Models\GiataPlace;
use App\Models\GiataProperty;
use App\Models\MapperHbsiGiata;
use Illuminate\Support\Facades\DB;

class HbsiRepository
{

    /**
     * @param string $input
     * @param int $limit
     * @param int $offset
     * @return array|null
     */
    public static function getIdsByDestinationGiata(string $input, int $limit = 100, int $offset = 1): ?array
    {
        $mainDB = config('database.connections.mysql.database');
        $cacheDB = config('database.connections.mysql_cache.database');

        $results = DB::table($cacheDB . '.giata_properties')
            ->join($mainDB . '.mapper_hbsi_giatas', $cacheDB . '.giata_properties.code', '=', $mainDB . '.mapper_hbsi_giatas.giata_id')
            ->where(is_numeric($input) ? 'city_id' : 'city', $input)
            ->select($cacheDB . '.giata_properties.code as giata', $cacheDB . '.giata_properties.name', $mainDB . '.mapper_hbsi_giatas.hbsi_id as hbsi')
            ->get()
            ->mapWithKeys(function ($value) {
                return [
                    $value->hbsi => [
                        'giata' => $value->giata,
                        'name' => $value->name,
                        'hbsi' => $value->hbsi,
                    ]
                ];
            })
            ->toArray();

        $totalResults = count($results);
        $totalPages = ceil($totalResults / $limit);

        $offset = $offset > 1 ? ($offset -1) * $limit : 0;
        $result = array_slice($results, $offset , $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

    public static function getIdsByGiataPlace(string $place, int $limit = 100, int $offset = 1): ?array
    {
        $results = MapperHbsiGiata::whereIn('giata_id', GiataPlace::where('key', $place)->select('tticodes')->first()->tticodes)
            ->get()
            ->mapWithKeys(function ($value) {
                return [
                    $value['hbsi_id'] => [
                        'giata' => $value['giata_id'],
                        'hbsi' => $value['hbsi_id'],
                    ]
                ];
            })
            ->toArray();

        $totalResults = count($results);
        $totalPages = ceil($totalResults / $limit);

        $offset = $offset > 1 ? ($offset -1) * $limit : 0;
        $result = array_slice($results, $offset , $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

}
