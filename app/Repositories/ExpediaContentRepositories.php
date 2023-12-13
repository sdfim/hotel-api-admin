<?php

namespace App\Repositories;

use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use Illuminate\Support\Collection;

class ExpediaContentRepositories
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
                if (!is_string($item->$key)) continue;
                if (str_contains($item->$key, '{')) {
                    $item->$key = json_decode($item->$key);
                }
            }

            return $item;
        });
    }

    /**
     * @param string $input
     * @return array
     */
    public static function getIdsByDestinationGiata(string $input): array
    {
        if ( is_numeric($input))  $query = GiataProperty::where('city_id', $input);
        else $query = GiataProperty::where('city', $input);

        return $query->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_expedia_giatas.expedia_id')
            ->get()
            ->pluck('expedia_id')
            ->toArray();
    }

    /**
     * @param $giata_id
     * @return mixed
     */
    public static function getDetailByGiataId($giata_id): object
    {
        return ExpediaContent::leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
            ->where('property_id', function($query) use ($giata_id) {
                $query->from('mapper_expedia_giatas')
                    ->leftJoin('giata_properties', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
                    ->select('mapper_expedia_giatas.expedia_id')
                    ->where('mapper_expedia_giatas.giata_id', $giata_id)
                    ->limit(1);
            })->get();
    }

    /**
     * @param int $hotel_id
     * @return string
     */
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
        foreach (json_decode($expedia->images, true) as $image) {
            if ($countImages == 5) break;
            $images[] = $image['links']['350px']['href'];
            $countImages++;
        }

        return $images;
    }

    /**
     * @param array $minMaxCoordinate
     * @return array
     */
    public static function getIdsByCoordinate(array $minMaxCoordinate): array
    {
        return GiataProperty::where('giata_properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where('giata_properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where('giata_properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where('giata_properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.giata_id', '=', 'giata_properties.code')
            ->select('mapper_expedia_giatas.expedia_id')
            ->whereNotNull('mapper_expedia_giatas.expedia_id')
            ->pluck('expedia_id')
            ->toArray();
    }
}
