<?php

namespace Modules\API\Tools;

use App\Models\Property;
use Google\Client;
use Google\Service\Exception;
use Google\Service\MapsPlaces;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1Place;
use GuzzleHttp\Psr7\Request;

class Geography
{
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
            'max_longitude' => $maxLongitude,
        ];
    }

    public function findTheClosestCityInRadius(float $latitude, float $longitude, float $radius): string|int|null
    {
        $destinationCoordinates = $this->calculateBoundingBox($latitude, $longitude, $radius);

        return Property::whereBetween('latitude', [
            $destinationCoordinates['min_latitude'], $destinationCoordinates['max_latitude'],
        ])
            ->whereBetween('longitude', [
                $destinationCoordinates['min_longitude'], $destinationCoordinates['max_longitude'],
            ])
            ->first()->city_id ?? null;
    }


    /**
     * @throws Exception
     * @throws \Google\Exception
     */
    public function getPlaceDetailById(string $id, string $session)
    {
        $client = new Client();
        $client->setDefer(true);
        $client->setApplicationName('OBE');
        $client->setDeveloperKey(env('GOOGLE_API_DEVELOPER_KEY'));

        $service = new MapsPlaces($client);

        /** @var Request $results */
        $request = $service->places->get("places/$id", [
            'sessionToken'  => $session,
        ]);

        $request = $request->withHeader('X-Goog-FieldMask', 'displayName,location');

        $place = $client->execute($request, GoogleMapsPlacesV1Place::class);

        return [
            'latitude'  => $place->getLocation()->latitude,
            'longitude' => $place->getLocation()->longitude,
        ];
    }
}
