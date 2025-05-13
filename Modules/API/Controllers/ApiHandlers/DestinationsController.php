<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\GiataPoi;
use Google\Client;
use Google\Service\Exception;
use Google\Service\MapsPlaces;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1AutocompletePlacesRequest;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1AutocompletePlacesResponseSuggestion;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1Place;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1PlaceAddressComponent;
use Google\Service\MapsPlaces\GoogleMapsPlacesV1SearchTextRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\API\Requests\DestinationRequest;
use Modules\Enums\SearchSuggestionStrategy;

class DestinationsController
{
    /**
     * @throws Exception
     */
    public function destinations(DestinationRequest $request): JsonResponse
    {
        if ($request->strategy === SearchSuggestionStrategy::Google->value && $request->giata === null) {
            return response()->json([
                'success' => true,
                'data' => $this->getGooglePlaceAutocompleteSuggestions($request),
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

    private function getGiataPoisData(DestinationRequest $request)
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

        if ($request->include !== null) {
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
            if (! empty($item['tticodes'])) {
                $destination = [
                    'full_name' => $item['poi_name_primary'].' ('.$item['place_name_primary'].')',
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

        if ($searchCriteria !== null) {
            $giataPlace->where(function ($query) use ($searchCriteria, $cleanSearchCriteria) {
                $query->where('name_primary', 'like', $searchCriteria);

                if (strlen($cleanSearchCriteria) === 3) {
                    $query->orWhereRaw('JSON_CONTAINS(`airports`, \'"'.strtoupper($cleanSearchCriteria).'"\', "$")');
                    //Original => $query->orWhere('airports', 'like', '%' . strtoupper($cleanSearchCriteria) . '%');
                }
            });
        } elseif ($request->giata !== null) {
            $giataPlace->where('tticodes', 'like', "%$request->giata%");
        }

        if ($request->include !== null) {
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
            if (! empty($item['tticodes'])) {
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

    private function getGiataGeographyData(DestinationRequest $request)
    {
        $query = GiataGeography::select(DB::raw('CONCAT(city_name, ", ", country_name, " (", country_code, ", ", locale_name, ")") AS full_name'), 'city_id');

        if (! empty($request->city)) {
            $cityParts = explode(' ', $request->city);
            foreach ($cityParts as $part) {
                $query->where('city_name', 'like', '%'.$part.'%');
            }
        }

        if (! empty($request->country)) {
            $countryParts = explode(' ', $request->country);
            foreach ($countryParts as $part) {
                $query->where('locale_name', 'like', '%'.$part.'%');
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

    private function getGooglePlaceTextSearchSuggestions(DestinationRequest $request)
    {
        $client = new Client();
        $client->setApplicationName('OBE');
        $client->setDeveloperKey(config('services.google.google_api_developer_key'));

        $service = new MapsPlaces($client);

        $searchCriteria = $request->q;

        if ($searchCriteria === null) {
            return collect();
        }

        // It's probably an airport
        if (strlen($searchCriteria) === 3) {
            $searchCriteria .= ' airport';
        }

        $params = new GoogleMapsPlacesV1SearchTextRequest();
        $params->textQuery = $searchCriteria;
        //$params->rankPreference = 'RELEVANCE';
        $params->languageCode = 'en';
        //$params->includedType = 'airport|hospital';//|hospital|library|museum|park|restaurant|shopping_mall|stadium|tourist_attraction|train_station|university|zoo';

        $results = $service->places->searchText($params, ['fields' => 'places.id,places.location,places.name,places.formattedAddress,places.displayName,places.primaryType,places.addressComponents']);

        return collect($results->getPlaces())->map(fn (GoogleMapsPlacesV1Place $place) => [
            'full_name' => $place->getDisplayName()->text,
            'place' => $place->getId(),
            'country_code' => collect($place->getAddressComponents())->filter(fn (GoogleMapsPlacesV1PlaceAddressComponent $component) => in_array('country', $component->types))->first()?->longText,
            'type' => match ($place->getPrimaryType()) {
                'airport' => 'Airport',
                'country' => 'Country',
                'continent' => 'Continent',
                'hotel' => 'Resort',
                default => 'Landmark',
            },
            'location' => [
                'latitude' => $place->getLocation()->latitude,
                'longitude' => $place->getLocation()->longitude,
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    private function getGooglePlaceAutocompleteSuggestions(DestinationRequest $request)
    {
        $client = new Client();
        $client->setApplicationName('OBE');
        $client->setDeveloperKey(config('services.google.google_api_developer_key'));

        $service = new MapsPlaces($client);
        $searchCriteria = $request->q;

        $primaryTypes = ['airport', 'hotel', 'resort_hotel', 'country', 'continent'];

        if ($searchCriteria === null) {
            return collect();
        }

        // It's probably an airport
        if (strlen($searchCriteria) === 3) {
            $searchCriteria .= ' airport';
            $primaryTypes = ['airport'];
        }

        $sessionToken = Str::uuid();

        $params = new GoogleMapsPlacesV1AutocompletePlacesRequest();
        $params->input = $searchCriteria;
        $params->sessionToken = $sessionToken;
        $params->includedPrimaryTypes = $primaryTypes;
        $results = $service->places->autocomplete($params);

        return collect($results->getSuggestions())->map(function (GoogleMapsPlacesV1AutocompletePlacesResponseSuggestion $place) use ($sessionToken)
        {
            $types = $place->getPlacePrediction()->getTypes();
            $type = 'Landmark';

            if (! empty(array_intersect(['hotel', 'resort_hotel', 'lodging'], $types)))
            {
                $type = 'Resort';
            }
            elseif (in_array('airport', $types))
            {
                $type = 'Airport';
            }
            elseif (in_array('country', $types))
            {
                $type = 'Country';
            }
            elseif (in_array('continent', $types))
            {
                $type = 'Continent';
            }

            return [
                 'full_name' => $place->getPlacePrediction()->getStructuredFormat()->getMainText()->getText(),
                 'place' => $place->getPlacePrediction()->getPlaceId(),
                 'session' => $sessionToken,
                 'country_code' => collect(explode(', ', $place->getPlacePrediction()->getText()->text))->last(),
                 'type' => $type,
                 'location' => null,
                ];
        });
    }

    private function getCleanSearchCriteria(?string $criteria): ?string
    {
        if ($criteria === null) {
            return null;
        }

        return str_replace('%', '', $criteria);
    }

    private function getSearchCriteria(?string $criteria): ?string
    {
        if ($criteria === null) {
            return null;
        }

        if (str_contains($criteria, '%')) {
            return $criteria;
        }

        return "$criteria%";
    }
}
