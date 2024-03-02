<?php

namespace App\Repositories;

use App\Models\GiataProperty;

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
        return GiataProperty::where(is_numeric($input) ? 'city_id' : 'city', $input)
            ->with('hbsi')
            ->select('code', 'name')
            ->limit($limit)
            ->offset($offset)
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
    }

}
