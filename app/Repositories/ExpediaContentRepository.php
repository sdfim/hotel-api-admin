<?php

namespace App\Repositories;

use App\Models\ExpediaContent;
use App\Models\GiataPlace;
use App\Models\GiataProperty;
use Illuminate\Support\Arr;
use App\Models\MapperExpediaGiata;
use Illuminate\Support\Collection;


class ExpediaContentRepository
{
    /**
     * @param $results
     * @param $fields
     * @return Collection
     */
    public static function dtoDbToResponse($results, $fields): Collection
    {
        return collect($results)->map(function ($item) use ($fields) {
            foreach ($fields as $key) {
                if (!is_string($item->$key)) {
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

        $expedia_id = MapperExpediaGiata::whereIn('giata_id', $tticodes)
            ->select('expedia_id')
            ->get()
            ->pluck('expedia_id')
            ->toArray();

        return $expedia_id;
    }

    /**
     * @param string $input
     * @return array
     */
    public static function getIdsByDestinationGiata(string $input): array
    {
        if (is_numeric($input)) {
            $query = GiataProperty::where('city_id', $input);
        } else {
            $query = GiataProperty::where('city', $input);
        }

        $mainDB = config('database.connections.mysql.database');

        return $query->leftJoin($mainDB . '.mapper_expedia_giatas', $mainDB . '.mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select($mainDB . '.mapper_expedia_giatas.expedia_id')
            ->whereNotNull($mainDB . '.mapper_expedia_giatas.expedia_id')
            ->get()
            ->pluck('expedia_id')
            ->toArray();
    }

    /**
     * @return mixed
     */
    public static function getDetailByGiataId($giata_id): object
    {
        $mainDB = config('database.connections.mysql.database');

        return ExpediaContent::leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
            ->where('property_id', function ($query) use ($giata_id, $mainDB) {
                $query->from($mainDB.'.mapper_expedia_giatas')
                    ->leftJoin('giata_properties', $mainDB.'.mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
                    ->select($mainDB.'.mapper_expedia_giatas.expedia_id')
                    ->where($mainDB.'.mapper_expedia_giatas.giata_id', $giata_id)
                    ->limit(1);
            })->get();
    }

    public static function getHotelNameByHotelId(int $hotel_id): string
    {
        return ExpediaContent::where('property_id', $hotel_id)
            ->select('name')
            ->first()
            ->name;
    }

    /**
     * @param int $hotel_id
     * @return array
     */
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

            if (Arr::has($image, 'links'))
            {
                $images[] = $image['links']['350px']['href'];
                $countImages++;
            }
        }

        return $images;
    }

    /**
     * @param array $minMaxCoordinate
     * @return array
     */
    public static function getIdsByCoordinate(array $minMaxCoordinate): array
    {
        $mainDB = config('database.connections.mysql.database');
        $cacheDB = config('database.connections.mysql_cache.database');

        return GiataProperty::where($cacheDB . '.giata_properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where($cacheDB . '.giata_properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where($cacheDB . '.giata_properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where($cacheDB . '.giata_properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin($mainDB . '.mapper_expedia_giatas', $mainDB . '.mapper_expedia_giatas.giata_id', '=', $cacheDB . '.giata_properties.code')
            ->select($mainDB . '.mapper_expedia_giatas.expedia_id')
            ->whereNotNull($mainDB . '.mapper_expedia_giatas.expedia_id')
            ->pluck('expedia_id')
            ->toArray();
    }
}
