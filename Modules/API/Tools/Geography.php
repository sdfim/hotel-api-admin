<?php

namespace Modules\API\Tools;

use App\Models\GiataProperty;

class Geography
{
    /**
     * @param float $latitude
     * @param float $longitude
     * @param float $radius
     * @return array
     */
    public function calculateBoundingBox(float $latitude, float $longitude, float $radius): array
    {
        $earthRadius = 6371;

        $radiusInRadians = $radius / $earthRadius;

        $latitude = deg2rad($latitude);
        $longitude = deg2rad($longitude);

        $minLatitude = $latitude - $radiusInRadians;
        $maxLatitude = $latitude + $radiusInRadians;

        $minLongitude = $longitude - $radiusInRadians;
        $maxLongitude = $longitude + $radiusInRadians;

        $minLatitude = rad2deg($minLatitude);
        $maxLatitude = rad2deg($maxLatitude);
        $minLongitude = rad2deg($minLongitude);
        $maxLongitude = rad2deg($maxLongitude);

        return [
            'min_latitude' => $minLatitude,
            'max_latitude' => $maxLatitude,
            'min_longitude' => $minLongitude,
            'max_longitude' => $maxLongitude
        ];
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param float $radius
     * @return string|int|null
     */
    public function findTheClosestDestinationInRadius(float $latitude, float $longitude, float $radius): string|int|null
    {
        $destinationCoordinates = $this->calculateBoundingBox($latitude, $longitude, $radius);

        return GiataProperty::whereBetween('latitude', [
            $destinationCoordinates['min_latitude'], $destinationCoordinates['max_latitude']
        ])
            ->whereBetween('longitude', [
                $destinationCoordinates['min_longitude'], $destinationCoordinates['max_longitude']
            ])
            ->first()->city_id ?? null;
    }
}
