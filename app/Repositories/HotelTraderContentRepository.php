<?php

namespace App\Repositories;

use App\Models\GiataPlace;
use App\Models\HotelTraderContentHotel;
use App\Models\Mapping;
use App\Models\Property;
use Illuminate\Support\Collection;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class HotelTraderContentRepository
{
    public static function dtoDbToResponse($results, $fields): Collection
    {
        return collect($results)->map(function ($item) use ($fields) {
            foreach ($fields as $key) {
                if (! is_string($item->$key)) {
                    continue;
                }
                if (str_contains($item->$key, '{')) {
                    $item->$key = json_decode($item->$key);
                }
            }

            return $item;
        });
    }

    public static function getIdsByGiataPlace(string $place): array
    {
        $tticodes = GiataPlace::where('key', $place)
            ->select('tticodes')
            ->first()
            ->tticodes;

        $expedia_id = Mapping::hotelTrader()->whereIn('giata_id', $tticodes)
            ->select('supplier_id')
            ->get()
            ->pluck('supplier_id')
            ->toArray();

        return $expedia_id;
    }

    public static function getIdsByGiataIds(array $giataIds): array
    {
        $expedia_id = Mapping::hotelTrader()->whereIn('giata_id', $giataIds)
            ->select('supplier_id')
            ->get()
            ->pluck('supplier_id')
            ->toArray();

        return $expedia_id;
    }

    public static function getIdsByDestinationGiata(string $input): array
    {
        if (is_numeric($input)) {
            $query = Property::where('city_id', $input);
        } else {
            $query = Property::where('city', $input);
        }

        $mainDB = config('database.connections.mysql.database');

        return $query->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.giata_id', '=', 'properties.code')
            ->select($mainDB.'.mappings.supplier_id')
            ->whereNotNull($mainDB.'.mappings.supplier_id')
            ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::HOTEL_TRADER->value)
            ->get()
            ->pluck('supplier_id')
            ->toArray();
    }

    /**
     * @return mixed
     */
    public static function getDetailByGiataId($giata_id): object
    {
        $mainDB = config('database.connections.mysql.database');

        return HotelTraderContentHotel::where('code', function ($query) use ($giata_id, $mainDB) {
            $query->from($mainDB.'.mappings')
                ->leftJoin('properties', $mainDB.'.mappings.giata_id', '=', 'properties.code')
                ->select($mainDB.'.mappings.supplier_id')
                ->where($mainDB.'.mappings.giata_id', $giata_id)
                ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::HOTEL_TRADER->value)
                ->limit(1);
        })->get();
    }

    public static function getHotelNameByHotelId(int $hotel_id): string
    {
        return HotelTraderContentHotel::where('code', $hotel_id)
            ->select('name')
            ->first()
            ->name;
    }

    public static function getIdsByCoordinate(array $minMaxCoordinate): array
    {
        $mainDB = config('database.connections.mysql.database');
        $cacheDB = config('database.connections.mysql_cache.database');

        return Property::where($cacheDB.'.properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where($cacheDB.'.properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where($cacheDB.'.properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where($cacheDB.'.properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.giata_id', '=', $cacheDB.'.properties.code')
            ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::HOTEL_TRADER->value)
            ->select($mainDB.'.mappings.supplier_id')
            ->whereNotNull($mainDB.'.mappings.supplier_id')
            ->pluck('supplier_id')
            ->toArray();
    }
}
