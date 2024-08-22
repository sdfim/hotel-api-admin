<?php

namespace App\Repositories;

use App\Models\GiataProperty;
use Illuminate\Support\Facades\DB;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

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
            ->leftJoin("$mainDB.mappings", "$cacheDB.ice_hbsi_properties.code", '=', "{$mainDB}.mappings.supplier_id")
            ->where($mainDB . '.mappings.supplier', MappingSuppliersEnum::IcePortal->value)
            ->where('city', $city)
            ->select("$cacheDB.ice_hbsi_properties.*", "$mainDB.mappings.*")
            ->get()
            ->map(function ($value) {
                return (array) $value;
            })
            ->toArray();

        foreach ($results as &$result) {
            if (isset($result['mappings']) && $result['mappings'] != null) {
                $result['giata_id'] = $result['mappings'][0]['giata_id'];
                $result['perc'] = $result['mappings'][0]['match_percentage'];
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

        return $query->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.giata_id', '=', 'giata_properties.code')
            ->where($mainDB . '.mappings.supplier', MappingSuppliersEnum::IcePortal->value)
            ->select($mainDB.'.mappings.supplier_id')
            ->whereNotNull($mainDB.'.mappings.supplier_id')
            ->get()
            ->pluck('supplier_id')
            ->toArray();
    }

    public static function getIdByCoordinate(array $minMaxCoordinate): int
    {
        $mainDB = config('database.connections.mysql.database');

        return GiataProperty::where('giata_properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('giata_properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('giata_properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('giata_properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.giata_id', '=', 'giata_properties.code')
            ->where($mainDB . '.mappings.supplier', MappingSuppliersEnum::IcePortal->value)
            ->select($mainDB.'.mappings.supplier_id')
            ->whereNotNull($mainDB.'.mappings.supplier_id')
            ->first()
            ->supplier_id ?? 0;
    }
}
