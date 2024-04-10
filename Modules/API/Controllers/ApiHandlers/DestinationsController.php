<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\GiataGeography;
use App\Models\GiataPlace;
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
            $response = $this->getGiataPlacesData($request);
        }

        if ($request->city !== null || $request->country !== null) {
            $response = $this->getGiataGeographyData($request);
        }

        return response()->json($response);
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
                $giataPlace->where('name_primary', 'like', '%' . $part . '%')
                    ->orWhere('airports', 'like', '%' . $part . '%');
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
                    'key' => $item['key'],
                    'type' => $item['type'],
                ];

                if ($request->showctticodes === '1') {
                    $destination['tticodes'] = $item['tticodes'];
                }

                $destinations[] = $destination;
            }
        }

        $response = [
            'success' => true,
            'data' => $destinations,
        ];

        return $response;
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
