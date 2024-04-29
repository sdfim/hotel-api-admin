<?php

namespace App\Repositories;

use App\Models\GiataPlace;
use App\Models\GiataProperty;
use App\Models\MapperHbsiGiata;

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
        $results = GiataProperty::where(is_numeric($input) ? 'city_id' : 'city', $input)
            ->with('hbsi')
            ->select('code', 'name')
            ->get()
            ->filter(function ($value) {
                return !is_null($value['hbsi']);
            })
            ->mapWithKeys(function ($value) {
                return [
                    $value['hbsi']['hbsi_id'] => [
                        'giata' => $value['code'],
                        'name' => $value['name'],
                        'hbsi' => $value['hbsi']['hbsi_id'],
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

        $result = array_slice($results, $offset, $limit);
        $associativeArray = array_column($result, null, 'hbsi');

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }

}
