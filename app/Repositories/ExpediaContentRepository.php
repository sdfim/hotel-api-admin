<?php

namespace App\Repositories;

use App\Models\ExpediaContent;
use App\Models\GiataPlace;
use App\Models\Mapping;
use App\Models\Property;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class ExpediaContentRepository
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

        $expedia_id = Mapping::expedia()->whereIn('giata_id', $tticodes)
            ->select('supplier_id')
            ->get()
            ->pluck('supplier_id')
            ->toArray();

        return $expedia_id;
    }

    public static function getIdsByGiataIds(array $giataIds): array
    {
        $expedia_id = Mapping::expedia()->whereIn('giata_id', $giataIds)
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
            ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::Expedia->value)
            ->get()
            ->pluck('supplier_id')
            ->toArray();
    }

    /**
     * @return mixed
     */
    public static function getDetailByGiataIds(array $giata_ids)
    {
        $mainDB = config('database.connections.mysql.database');

        return ExpediaContent::leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
            ->whereIn('property_id', function ($query) use ($giata_ids, $mainDB) {
                $query->from($mainDB.'.mappings')
                    ->leftJoin('properties', $mainDB.'.mappings.giata_id', '=', 'properties.code')
                    ->select($mainDB.'.mappings.supplier_id')
                    ->whereIn($mainDB.'.mappings.giata_id', $giata_ids)
                    ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::Expedia->value);
            })
            ->get();
    }

    public static function getHotelNameByHotelId(int $hotel_id): string
    {
        return ExpediaContent::where('property_id', $hotel_id)
            ->select('name')
            ->first()
            ->name;
    }

    public static function getHotelImagesByHotelId(int $hotel_id): array
    {
        $expedia = ExpediaContent::where('property_id', $hotel_id)
            ->leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
            ->select('expedia_content_slave.images as images')
            ->first();

        $images = [];
        $countImages = 0;
        foreach ((json_decode($expedia->images, true) ?? []) as $image) {
            if ($countImages == 5) {
                break;
            }

            if (Arr::has($image, 'links')) {
                $images[] = $image['links']['350px']['href'];
                $countImages++;
            }
        }

        return $images;
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
            ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::Expedia->value)
            ->select($mainDB.'.mappings.supplier_id')
            ->whereNotNull($mainDB.'.mappings.supplier_id')
            ->pluck('supplier_id')
            ->toArray();
    }
}
