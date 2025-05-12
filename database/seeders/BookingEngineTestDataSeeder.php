<?php

namespace Database\Seeders;

use App\Models\Mapping;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class BookingEngineTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->hbsi();
    }

    public function hbsi()
    {
        $hotels = [
            // Cancun
            [
                'code' => 1004,
                'name' => 'Nizuc Resort & Spa Test',
                'city' => 'Cancun',
                'city_id' => 508,
                'locale' => 'Yucatan Peninsula',
                'locale_id' => 1026,
                'giata_id' => 1004,
                'supplier_id' => 51721,
                'latitude' => 21.03399425,
                'longitude' => -86.78897201,
                'country_code' => 'MX',
            ],
            [
                'code' => 1005,
                'name' => 'Garza Blanca Cancun Test',
                'city' => 'Cancun',
                'city_id' => 508,
                'locale' => 'Yucatan Peninsula',
                'locale_id' => 1026,
                'giata_id' => 1005,
                'supplier_id' => 51721,
                'latitude' => 21.03399425,
                'longitude' => -86.78897201,
                'country_code' => 'MX',
            ],
            [
                'code' => 1006,
                'name' => 'Grand Velas Riviera Maya Test',
                'city' => 'Cancun',
                'city_id' => 508,
                'locale' => 'Yucatan Peninsula',
                'locale_id' => 1026,
                'giata_id' => 1006,
                'supplier_id' => 51721,
                'latitude' => 21.03399425,
                'longitude' => -86.78897201,
                'country_code' => 'MX',
            ],
            [
                'code' => 1007,
                'name' => 'Banyan Tree Mayakoba Test',
                'city' => 'Cancun',
                'city_id' => 508,
                'locale' => 'Yucatan Peninsula',
                'locale_id' => 1026,
                'giata_id' => 1007,
                'supplier_id' => 51721,
                'latitude' => 21.03399425,
                'longitude' => -86.78897201,
                'country_code' => 'MX',
            ],
            // St. Lucia
            [
                'code' => 1008,
                'name' => 'Sugar Beach Test',
                'city' => 'St. Lucia',
                'city_id' => 860,
                'locale' => 'St. Lucia',
                'locale_id' => 137,
                'giata_id' => 1008,
                'supplier_id' => 51722,
                'latitude' => 13.827802702355,
                'longitude' => -61.061111035721,
                'country_code' => 'LC',
            ],
            // Bahamas
            [
                'code' => 1009,
                'name' => 'The Cove Atlantis Test',
                'city' => 'Bahamas',
                'city_id' => 11430,
                'locale' => 'Bahamas Islands',
                'locale_id' => 8,
                'giata_id' => 1009,
                'supplier_id' => 48187,
                'latitude' => 25.0863576,
                'longitude' => -77.3295836,
                'country_code' => 'BS',
            ],
            [
                'code' => 1010,
                'name' => 'The Royal Atlantis Test',
                'city' => 'Bahamas',
                'city_id' => 11430,
                'locale' => 'Bahamas Islands',
                'locale_id' => 8,
                'giata_id' => 1010,
                'supplier_id' => 48187,
                'latitude' => 25.084634,
                'longitude' => -77.32379,
                'country_code' => 'BS',
            ],
            [
                'code' => 1011,
                'name' => 'The Reef Atlantis Test',
                'city' => 'Bahamas',
                'city_id' => 11430,
                'locale' => 'Bahamas Islands',
                'locale_id' => 8,
                'giata_id' => 1011,
                'supplier_id' => 48187,
                'latitude' => 25.0851894,
                'longitude' => -77.3283573,
                'country_code' => 'BS',
            ],
        ];

        foreach ($hotels as $hotel) {
            $propertyData = [
                'code' => $hotel['code'],
                'name' => $hotel['name'],
                'chain' => null,
                'city' => $hotel['city'],
                'city_id' => $hotel['city_id'],
                'locale' => $hotel['locale'],
                'locale_id' => $hotel['locale_id'],
                'address' => [
                    'UseType' => '7',
                    'CityName' => $hotel['city'],
                    'PostalCode' => '00000',
                    'AddressLine' => '',
                    'CountryName' => $hotel['country_code'],
                    'FormattedInd' => 'true',
                ],
                'mapper_address' => $hotel['locale'],
                'mapper_postal_code' => 77400,
                'mapper_phone_number' => '',
                'phone' => [],
                'position' => [],
                'latitude' => $hotel['latitude'],
                'longitude' => $hotel['longitude'],
                'url' => [],
                'cross_references' => [],
                'rating' => 5,
                'source' => 'Custom',
                'property_auto_updates' => 0,
                'content_auto_updates' => 0,
            ];

            Property::updateOrCreate(
                Arr::only($propertyData, ['code', 'name']),
                $propertyData
            );

            Mapping::firstOrCreate([
                'giata_id' => $hotel['giata_id'],
                'supplier' => MappingSuppliersEnum::HBSI->value,
                'supplier_id' => $hotel['supplier_id'],
            ], [
                'match_percentage' => 100,
            ]);
        }
    }

}
