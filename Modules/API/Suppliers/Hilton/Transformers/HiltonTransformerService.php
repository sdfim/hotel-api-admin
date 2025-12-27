<?php

namespace Modules\API\Suppliers\Hilton\Transformers;

use Illuminate\Support\Arr;

class HiltonTransformerService
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

    public function mapPolicies(array $policies): array
    {
        foreach ($policies as $policyName => $policyDetails) {
            if (! is_array($policyDetails)) {
                continue;
            }
            foreach ($policyDetails as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $transformedPolicies[] = [
                            'name' => "policy_{$policyName}_{$subKey}",
                            'value' => $subValue,
                            'start_date' => null,
                            'end_date' => null,
                        ];
                    }
                } else {
                    $transformedPolicies[] = [
                        'name' => "policy_{$policyName}_{$key}",
                        'value' => $value,
                        'start_date' => null,
                        'end_date' => null,
                    ];
                }
            }
        }

        return $transformedPolicies;
    }

    public function mapTaxes(?array $taxes)
    {
        if (! $taxes) {
            return [];
        }
        $res = array_map(function ($taxData) {
            $startDate = $taxData['startDate'] ?? null;
            $endDate = $taxData['endDate'] ?? null;

            return array_map(function ($tax) use ($startDate, $endDate) {
                return [
                    'name' => 'hotel_fees',
                    'value' => $tax['taxAmount'].' '.$tax['taxTypeDescription'].' '.$tax['taxBasisDescription'].' '.$tax['taxPeriodDescription'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];
            }, $taxData['taxes']);
        }, $taxes);

        return Arr::get($res, 0, []);
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

        $description[] = ['name' => 'description', 'value' => $property->long_description ?? ''];
        $checkIn = ['name' => 'checkin', 'value' => $property->check_in_time ?? ''];
        $checkOut = ['name' => 'checkout', 'value' => $property->check_out_time ?? ''];

//        $policy = $this->mapPolicies($property?->policy);
//        $taxes = $this->mapTaxes($property?->taxes);
//        $description = array_values(array_merge($policy, $taxes));
        $description[] = $checkIn;
        $description[] = $checkOut;


//        dd($property, $description);

        return $description;
    }
}
