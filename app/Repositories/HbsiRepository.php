<?php

namespace App\Repositories;

use App\Models\GiataPlace;
use App\Models\Mapping;
use Illuminate\Support\Facades\DB;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class HbsiRepository
{
    public static function getIdsByDestinationGiata(string $input, int $limit = 100, int $page = 1, array $filters = []): ?array
    {
        $mainDB = config('database.connections.mysql.database');
        $cacheDB = config('database.connections.mysql_cache.database');

        $results = DB::table($cacheDB.'.properties')
            ->join($mainDB.'.mappings', $cacheDB.'.properties.code', '=', $mainDB.'.mappings.giata_id')
            ->where(is_numeric($input) ? 'city_id' : 'city', $input)
            ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::HBSI->value)
            ->select($cacheDB.'.properties.code as giata', $cacheDB.'.properties.name', $mainDB.'.mappings.supplier_id as hbsi');

        if (isset($filters['hotel_name'])) {
            $results->where($cacheDB.'.properties.hotel_name', 'like', '%'.$filters['hotel_name'].'%');
        }

        $results = $results->get()
            ->mapWithKeys(function ($value) {
                return [
                    $value->hbsi => [
                        'giata' => $value->giata,
                        'name' => $value->name,
                        'hbsi' => $value->hbsi,
                    ],
                ];
            })
            ->toArray();

        $totalResults = count($results);
        $totalPages = ceil($totalResults / $limit);

        $offset = $page > 1 ? ($page - 1) * $limit : 0;
        $result = array_slice($results, $offset, $limit);
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
     * @param  string  $place  The name of the Giata place.
     * @param  int  $limit  An optional parameter indicating the maximum number of results per page. Defaults to 100.
     * @param  int  $page  An optional parameter indicating the page number. Defaults to 1.
     * @return array|null Returns an array with identifiers or null if there are no results.
     */
    public static function getIdsByGiataPlace(string $place, int $limit = 100, int $page = 1): ?array
    {
        $results = Mapping::hBSI()->whereIn('giata_id', GiataPlace::where('key', $place)->select('tticodes')->first()->tticodes)
            ->get()
            ->mapWithKeys(function ($value) {
                return [
                    $value['supplier_id'] => [
                        'giata' => $value['giata_id'],
                        'hbsi' => $value['supplier_id'],
                    ],
                ];
            })
            ->toArray();

        $totalResults = count($results);
        $totalPages = ceil($totalResults / $limit);

        // If $page is greater than 1, adjust it to match zero-based array indexing
        // Otherwise, leave $offset as 0
        $offset = $page > 1 ? ($page - 1) * $limit : 0;
        // Extract a portion of $results array starting from $offset and containing $limit number of items
        $result = array_slice($results, $offset, $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

    public static function getIdsByCoordinate(array $minMaxCoordinate, int $limit = 100, int $page = 1, $filters = []): array
    {
        $mainDB = config('database.connections.mysql.database');
        $cacheDB = config('database.connections.mysql_cache.database');

        $results = DB::table($cacheDB.'.properties')
            ->where($cacheDB.'.properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where($cacheDB.'.properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where($cacheDB.'.properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where($cacheDB.'.properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->join($mainDB.'.mappings', $cacheDB.'.properties.code', '=', $mainDB.'.mappings.giata_id')
            ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::HBSI->value)
            ->select($cacheDB.'.properties.code as giata', $cacheDB.'.properties.name', $mainDB.'.mappings.supplier_id as hbsi');

        if (isset($filters['hotel_name'])) {
            $results->where($cacheDB.'.properties.name', 'like', '%'.$filters['hotel_name'].'%');
        }

        $results = $results->get()
            ->mapWithKeys(function ($value) {
                return [
                    $value->hbsi => [
                        'giata' => $value->giata,
                        'name' => $value->name,
                        'hbsi' => $value->hbsi,
                    ],
                ];
            })
            ->toArray();

        $totalResults = count($results);
        $totalPages = ceil($totalResults / $limit);

        $offset = $page > 1 ? ($page - 1) * $limit : 0;
        $result = array_slice($results, $offset, $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

    public static function getIdsByGiataIds(array $giataIds, int $limit = 100, int $page = 1): ?array
    {
        $results = Mapping::hBSI()->whereIn('giata_id', $giataIds)
            ->get()
            ->mapWithKeys(function ($value) {
                return [
                    $value['supplier_id'] => [
                        'giata' => $value['giata_id'],
                        'hbsi' => $value['supplier_id'],
                    ],
                ];
            })
            ->toArray();

        $totalResults = count($results);
        $totalPages = ceil($totalResults / $limit);

        // If $page is greater than 1, adjust it to match zero-based array indexing
        // Otherwise, leave $offset as 0
        $offset = $page > 1 ? ($page - 1) * $limit : 0;
        // Extract a portion of $results array starting from $offset and containing $limit number of items
        $result = array_slice($results, $offset, $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }
}
