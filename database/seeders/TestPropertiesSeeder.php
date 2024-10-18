<?php

namespace Database\Seeders;

use App\Models\Mapping;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class TestPropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->hbsi();
    }

    public function hbsi()
    {
        // Create Test Properties
        $propertyCommonData = [
            'chain' => NULL,
            'city' => 'Cancun',
            'city_id' => 508,
            'locale' => 'Yucatan Peninsula',
            'locale_id' => 1026,
            'address' => json_decode('{"UseType":"7","CityName":"Cancún","PostalCode":"77500","AddressLine":"Blvd Kukulcan","CountryName":"MX","FormattedInd":"true"}', true),
            'mapper_address' => 'Yucatan Peninsula',
            'mapper_postal_code' => 77400,
            'mapper_phone_number' => '',
            'phone' => [],
            'position' => [],
            'latitude' => 21.03399425,
            'longitude' => -86.78897201,
            'url' => [],
            'cross_references' => [],
            'rating' => 5,
            'source' => 'Custom',
            'property_auto_updates' => 0,
            'content_auto_updates' => 0,
        ];

        $propertiesToCreate = [
            [
                'code' => 1001,
                'name' => 'Test Property 1',
                ...$propertyCommonData
            ],
            [
                'code' => 1002,
                'name' => 'Test Property 2',
                ...$propertyCommonData
            ],
            [
                'code' => 1003,
                'name' => 'Test Property 3',
                ...$propertyCommonData
            ]
        ];

        foreach($propertiesToCreate as $propertyData)
        {
            $property = Property::firstOrNew(Arr::only($propertyData, ['code', 'name']), $propertyData);
            $property->save();
        }

        //Create Test Properties Mappings
        $mappingsData = [
            [
                'giata_id' => 1001,
                'supplier' => MappingSuppliersEnum::HBSI->value,
                'supplier_id' => 51721,
                'match_percentage' => 100,
            ],
            [
                'giata_id' => 1002,
                'supplier' => MappingSuppliersEnum::HBSI->value,
                'supplier_id' => 51722,
                'match_percentage' => 100,
            ],
            [
                'giata_id' => 1003,
                'supplier' => MappingSuppliersEnum::HBSI->value,
                'supplier_id' => 48187,
                'match_percentage' => 100,
            ]
        ];

        foreach($mappingsData as $mappingData)
        {
            $mapping = Mapping::firstOrNew($mappingData);
            $mapping->save();
        }
    }
}
