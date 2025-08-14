<?php

namespace Modules\API\Suppliers\Transformers\HotelTrader;

use Illuminate\Support\Arr;

class HotelTraderTransformerService
{
    public function mapAmenities(array $propertyAttributes): array
    {
        $amenities = [];

        foreach ($propertyAttributes as $key => $value) {
            if ($value === true) {
                $name = ucwords(str_replace(['has', 'provides', 'wheelChair'], ['', '', 'Wheelchair'], $key));
                $name = preg_replace('/([A-Z])/', ' $1', $name);
                $name = trim($name);

                $category = str_contains($key, 'Accessible') ? 'accessible_wheelchair' : 'general';

                $amenities[] = [
                    'name' => $name,
                    'category' => $category,
                ];
            }
        }

        return $amenities;
    }

    public function mapAirportData(array $airportData): array
    {
        return array_map(fn ($airport) => [
            'name' => Arr::get($airport, 'name', ''),
            'distance_km' => Arr::get($airport, 'airportDistance', 0),
        ], $airportData);
    }

    public function mapDescriptions(array|object $property): array
    {
        if (is_array($property)) {
            $property = (object) $property;
        }

        $description[] = ['name' => 'description', 'value' => $property->longDescription];
        $checkIn = ['name' => 'checkin', 'value' => $property->check_in_time ?? ''];
        $checkOut = ['name' => 'checkout', 'value' => $property->check_out_time ?? ''];

        $description[] = $checkIn;
        $description[] = $checkOut;

        return $description;
    }
}
