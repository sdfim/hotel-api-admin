<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
use App\Models\GiataPoi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\API\Requests\DestinationResponse;

class DestinationsController {
    /**
     * @param DestinationResponse $request
     * @return JsonResponse
     */
    public function destinations(DestinationResponse $request): JsonResponse
    {
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

    private function  getGiataPoisData($request)
    {
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
            ->join('giata_places', function ($join) use ($request) {
                $poi = GiataPoi::where('name_primary', 'like', '%' . $request->q . '%')->first();
                if ($poi !== null) {
                    $places = $poi->places;
                    $join->whereIn('giata_places.key', $places);
                }
            });

        $queryParts = explode(' ', $request->q);
        foreach ($queryParts as $part) {
            $giataPois->where('giata_pois.name_primary', 'like', '%' . $part . '%');
        }

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
                    'full_name' => $item['poi_name_primary'] . ' (' . $item['place_name_primary'] . ')'
                        . (!empty($item['country_code']) ? ', country ' . $item['country_code'] : '')
                        . (!empty($item['state']) ? ', state ' . $item['state'] : '')
                        . (!empty($item['airports']) ? ', airport ' . implode(', ', $item['airports']) : ''),
                    'place' => $item['key'],
                    'type' => $item['poi_type'],
                ];

                if ($request->showtticodes === '1') {
                    $destination['tticodes'] = $item['tticodes'];
                }

                $destinations[] = $destination;
            }
        }

        return $destinations;
    }

    private function getGiataPlacesData($request)
    {
        $giataPlace = GiataPlace::select('name_primary', 'key', 'type', 'tticodes', 'airports', 'country_code', 'state');

        $cityParts = explode(' ', $request->q);
        foreach ($cityParts as $part) {
            if (strlen($part) == 3 && ctype_upper($part)) {
                // If the part is 3 characters long and all uppercase, search only by airport
                $giataPlace->where('airports', 'like', '%' . $part . '%');
            } else {
                $giataPlace->where('name_primary', 'like', '%' . $part . '%');
//                    ->orWhere('airports', 'like', '%' . $part . '%');

            }
        }

        $giataPlace = $giataPlace->limit(35)
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
                    'full_name' => $item['name_primary']
                        . (!empty($item['country_code']) ? ', country ' . $item['country_code'] : '')
                        . (!empty($item['state']) ? ', state ' . $item['state'] : '')
                        . (!empty($item['airports']) ? ', airport ' . implode(', ', $item['airports']) : ''),
                    'place' => $item['key'],
                    'type' => $item['type'],
                ];

                if ($request->showtticodes === '1') {
                    $destination['tticodes'] = $item['tticodes'];
                }

                $destinations[] = $destination;
            }
        }

        return $destinations;
    }

    private function getGiataGeographyData($request) {
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

}
