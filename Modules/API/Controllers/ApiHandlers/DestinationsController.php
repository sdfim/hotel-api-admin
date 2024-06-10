<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\GiataPoi;
use Google\Client;
use Google\Service\MapsPlaces;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\API\Requests\DestinationRequest;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1SearchTextRequest;
use Modules\Enums\SearchSuggestionStrategy;

class DestinationsController {
    /**
     * @param DestinationRequest $request
     * @return JsonResponse
     */
    public function destinations(DestinationRequest $request): JsonResponse
    {
        if ($request->strategy === SearchSuggestionStrategy::Google->value && $request->giata === null)
        {
            return response()->json([
                'success' => true,
                'data' => $this->getGooglePlaceSuggestions($request),
            ]);
        }

        if ($request->giata !== null) {
            $response = [
                'success' => true,
                'data' => $this->getGiataPlacesData($request),
            ];
        }

        if ($request->q !== null) {
            $giataPlaces = $this->getGiataPlacesData($request);
            $giataPois = $this->getGiataPoisData($request);

            $response = [
                'success' => true,
                'data' => array_merge($giataPlaces, $giataPois),
            ];
        }

        if ($request->city !== null || $request->country !== null) {
            $response = $this->getGiataGeographyData($request);
        }

        return response()->json($response);
    }

    private function  getGiataPoisData(DestinationRequest $request)
    {
        $searchCriteria = $this->getSearchCriteria($request->q);

        $giataPois = GiataPoi::select(
                'giata_pois.name_primary as poi_name_primary',
                'giata_places.name_primary as place_name_primary',
                'giata_places.key',
                'giata_pois.type as poi_type',
                'giata_places.type as place_type',
                'giata_places.tticodes',
                'giata_places.airports',
                'giata_places.country_code',
                'giata_places.state'
            )
            ->join('giata_places', function ($join) use ($searchCriteria) {
                $poi = GiataPoi::where('name_primary', 'like', $searchCriteria)->first();
                if ($poi !== null) {
                    $places = $poi->places;
                    $join->whereIn('giata_places.key', $places);
                }
            });

        if ($request->include !== null)
        {
            $giataPois->whereIn('giata_pois.type', $request->include);
        }

        $giataPois->where('giata_pois.name_primary', 'like', $searchCriteria);

        $giataPois = $giataPois
            ->limit(35)
            ->get()
            ->map(function ($item) {
                return [
                    'poi_name_primary' => $item->poi_name_primary,
                    'place_name_primary' => $item->place_name_primary,
                    'poi_type' => $item->poi_type,
                    'key' => $item->key,
                    'country_code' => $item->country_code,
                    'state' => $item->state,
                    'airports' => json_decode($item->airports, true),
                    'tticodes' => json_decode($item->tticodes, true),
                ];
            })
            ->toArray();

        $destinations = [];
        foreach ($giataPois as $item) {
            if (!empty($item['tticodes'])) {
                $destination = [
                    'full_name' => $item['poi_name_primary'] . ' (' . $item['place_name_primary'] . ')',
                    'place' => $item['key'],
                    'type' => $item['poi_type'],
                    'country_code' => $item['country_code'] ?? '',
                    'airports' => $item['airports'] ?? '',
                ];

                if ($request->showtticodes === '1') {
                    $destination['tticodes'] = $item['tticodes'];
                }

                $destinations[] = $destination;
            }
        }

        return $destinations;
    }

    private function getGiataPlacesData(DestinationRequest $request)
    {
        $giataPlace = GiataPlace::select('name_primary', 'key', 'type', 'tticodes', 'airports', 'country_code', 'state');
        $searchCriteria = $this->getSearchCriteria($request->q);
        $cleanSearchCriteria = $this->getCleanSearchCriteria($request->q);

        if ($searchCriteria !== null)
        {
            $giataPlace->where(function ($query) use ($searchCriteria, $cleanSearchCriteria)
            {
                $query->where('name_primary', 'like', $searchCriteria);

                if (strlen($cleanSearchCriteria) === 3)
                {
                    $query->orWhereRaw('JSON_CONTAINS(`airports`, \'"'. strtoupper($cleanSearchCriteria) .'"\', "$")');
                    //Original => $query->orWhere('airports', 'like', '%' . strtoupper($cleanSearchCriteria) . '%');
                }
            });
        }
        elseif ($request->giata !== null)
        {
            $giataPlace->where('tticodes', 'like', "%$request->giata%");
        }

        if ($request->include !== null)
        {
            $giataPlace->whereIn('type', $request->include);
        }

        $giataPlace = $giataPlace
            ->orderBy('type')
            ->limit(100)
            ->get()
            ->map(function ($item) {
                return [
                    'key' => $item->key,
                    'name_primary' => $item->name_primary,
                    'type' => $item->type,
                    'tticodes' => $item->tticodes,
                    'country_code' => $item->country_code,
                    'airports' => $item->airports,
                    'state' => $item->state,
                ];
            })
            ->toArray();

        $destinations = [];
        foreach ($giataPlace as $item) {
            if (!empty($item['tticodes'])) {
                $destination = [
                    'full_name' => $item['name_primary'],
                    'place' => $item['key'],
                    'type' => $item['type'],
                    'country_code' => $item['country_code'] ?? '',
                    'airports' => $item['airports'] ?? '',
                ];

                if ($request->showtticodes === '1') {
                    $destination['tticodes'] = $item['tticodes'];
                }

                $destinations[] = $destination;
            }
        }

        return $destinations;
    }

    private function getGiataGeographyData(DestinationRequest $request) {
        $query = GiataGeography::select(DB::raw('CONCAT(city_name, ", ", country_name, " (", country_code, ", ", locale_name, ")") AS full_name'), 'city_id');

        if (!empty($request->city)) {
            $cityParts = explode(' ', $request->city);
            foreach ($cityParts as $part) {
                $query->where('city_name', 'like', '%' . $part . '%');
            }
        }

        if (!empty($request->country)) {
            $countryParts = explode(' ', $request->country);
            foreach ($countryParts as $part) {
                $query->where('locale_name', 'like', '%' . $part . '%');
            }
        }

        $giataGeography = $query->limit(35)
            ->orderBy('city_id', 'asc')
            ->get()
            ->pluck('city_id', 'full_name')
            ->toArray();

        $destinations = [];
        foreach ($giataGeography as $key => $value) {
            $destinations[] = [
                'full_name' => $key,
                'city_id' => $value,
            ];
        }

        $response = [
            'success' => true,
            'data' => $destinations,
        ];

        return $response;
    }

    private function getGooglePlaceSuggestions(DestinationRequest $request)
    {
        $client = new Client();
        $client->setApplicationName("OBE");
        $client->setDeveloperKey("AIzaSyD2WsimQb0Xgu9vIYRrTa1nbS9CBEZBJC0");

        $service = new MapsPlaces($client);

        $searchCriteria = $request->q;

        if ($searchCriteria === null)
        {
            return collect();
        }

        // It's probably an airport
        if (strlen($searchCriteria) === 3)
        {
            $searchCriteria .= ' airport';
        }

        $params = new GoogleMapsPlacesV1SearchTextRequest();
        $params->textQuery = $searchCriteria;
        //$params->rankPreference = 'RELEVANCE';
        $params->languageCode = 'en';
        //$params->includedType = 'airport|hospital';//|hospital|library|museum|park|restaurant|shopping_mall|stadium|tourist_attraction|train_station|university|zoo';

        $results = $service->places->searchText($params, ['fields' => 'places.id,places.location,places.name,places.formattedAddress,places.displayName,places.primaryType']);

        return collect($results->getPlaces())->map(fn (GoogleMapsPlacesV1Place $place) => [
            'full_name' => $place->getDisplayName()->text,
            'place'     => $place->getId(),
            'type'      => match ($place->getPrimaryType())
            {
                'airport'   => 'Airport',
                'country'   => 'Country',
                'continent' => 'Continent',
                'hotel'     => 'Resort',
                default     => 'Landmark',
            },
            'location'  => [
                'latitude'  => $place->getLocation()->latitude,
                'longitude' => $place->getLocation()->longitude,
            ],
        ]);
    }

    private function getCleanSearchCriteria(?string $criteria): ?string
    {
        if ($criteria === null)
        {
            return null;
        }

        return str_replace('%', '', $criteria, );
    }

    private function getSearchCriteria(?string $criteria): ?string
    {
        if ($criteria === null)
        {
            return null;
        }

        if (str_contains($criteria, '%'))
        {
            return $criteria;
        }

        return "$criteria%";
    }
}
