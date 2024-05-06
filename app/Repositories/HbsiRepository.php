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
     * @param int $page
     * @return array|null
     */
    public static function getIdsByDestinationGiata(string $input, int $limit = 100, int $page = 1): ?array
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

        $offset = $page > 1 ? ($page -1) * $limit : 0;
        $result = array_slice($results, $offset , $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * The getIdsByGiataPlace method retrieves identifiers by Giata place.
     *
     * This method takes a place name as an input parameter and returns an array,
     * containing identifiers corresponding to that place. The method also supports pagination
     * of results using the parameters $limit and $offset.
     *
     * @param string $place The name of the Giata place.
     * @param int $limit An optional parameter indicating the maximum number of results per page. Defaults to 100.
     * @param int $page An optional parameter indicating the page number. Defaults to 1.
     * @return array|null Returns an array with identifiers or null if there are no results.
     */
    public static function getIdsByGiataPlace(string $place, int $limit = 100, int $page = 1): ?array
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

        // If $page is greater than 1, adjust it to match zero-based array indexing
        // Otherwise, leave $offset as 0
        $offset = $page > 1 ? ($page -1) * $limit : 0;
        // Extract a portion of $results array starting from $offset and containing $limit number of items
        $result = array_slice($results, $offset , $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

}
