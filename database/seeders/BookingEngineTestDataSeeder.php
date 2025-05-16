<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigAmenity;
use App\Models\Mapping;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\Enums\HotelSaleTypeEnum;
use Modules\Enums\ProductTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use Modules\HotelContentRepository\Models\ProductAffiliationAmenity;
use Modules\HotelContentRepository\Models\ProductDepositInformation;
use Modules\HotelContentRepository\Models\Vendor;

class BookingEngineTestDataSeeder extends Seeder
{
    protected array $hotelCodes = [];

    public function run(): void
    {
        $this->hotelCodes = $this->seedProperties();

        $vendor = $this->seedVendor();

        $this->seedHotelRooms();
        $this->seedProductsAndDeposits($vendor);
        $this->seedAmenities();
        $this->seedAffiliationsAndAmenities();
    }

    public function seedProperties(): array
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

            $property = Property::updateOrCreate(
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

            Hotel::updateOrCreate([
                'giata_code' => $hotel['code'],
            ], [
                'star_rating' => 5,
                'num_rooms' => 100,
                'address' => $property->address,
                'featured_flag' => 1,
                'sale_type' => HotelSaleTypeEnum::DIRECT_CONNECTION->value,
                'travel_agent_commission' => 10.0,
            ]);
        }

        return collect($hotels)->pluck('code')->toArray();
    }

    protected function seedHotelRooms(): void
    {
        $hotels = Hotel::whereIn('giata_code', $this->hotelCodes)->get();

        foreach ($hotels as $hotel) {
            for ($i = 1; $i <= 5; $i++) {
                HotelRoom::updateOrCreate([
                    'hotel_id' => $hotel->id,
                    'external_code' => (string) $hotel->giata_code,
                    'name' => "Room {$i} - {$hotel->giata_code}",
                ], [
                    'supplier_codes' => json_encode([
                    [
                        'code' => '314409641',
                        'supplier' => 'Expedia',
                    ]
                ]),
                    'description' => "Test Room {$i} for hotel giata code {$hotel->giata_code}",
                    'area' => rand(30, 80) . ' sqm',
                    'bed_groups' => ['King Bed', 'Double Bed'],
                    'room_views' => ['Ocean View', 'Garden View'],
                    'related_rooms' => [],
                ]);
            }
        }
    }

    protected function seedVendor(): Vendor
    {
        return Vendor::firstOrCreate([
            'name' => 'Booking Engine Vendor',
            'verified' => true,
        ]);
    }

    protected function seedProductsAndDeposits(Vendor $vendor): void
    {
        $hotels = Hotel::whereIn('giata_code', $this->hotelCodes)->get();
        $hotelCodes = $hotels->pluck('giata_code')->toArray();
        shuffle($hotelCodes);

        $firstTwo = array_slice($hotelCodes, 0, 2);
        $third = $hotelCodes[2] ?? null;

        foreach ($hotels as $hotel) {
            $product = Product::updateOrCreate([
                'related_id' => $hotel->id,
                'related_type' => Hotel::class,
            ], [
                'vendor_id' => $vendor->id,
                'product_type' => ProductTypeEnum::HOTEL->value,
                'name' => "Product for {$hotel->giata_code}",
                'verified' => true,
                'onSale' => true,
                'default_currency' => 'USD',
                'lat' => 0,
                'lng' => 0,
                'content_source_id' => 3,
                'property_images_source_id' => 4,
            ]);

            if (in_array($hotel->giata_code, $firstTwo)) {
                ProductDepositInformation::updateOrCreate([
                    'product_id' => $product->id,
                    'name' => 'Standard Deposit',
                ], [
                    'price_value' => 100,
                    'price_value_type' => 'fixed',
                    'price_value_target' => 'booking',
                    'manipulable_price_type' => 'fixed',
                    'start_date' => now(),
                    'expiration_date' => now()->addYears(5),
                ]);
            }

            if ($third && $hotel->giata_code == $third) {
                ProductDepositInformation::updateOrCreate([
                    'product_id' => $product->id,
                    'name' => 'Percentage Deposit',
                ], [
                    'price_value' => 50,
                    'price_value_type' => 'percentage',
                    'price_value_target' => 'booking',
                    'manipulable_price_type' => 'percentage',
                    'start_date' => now(),
                    'expiration_date' => now()->addYears(5),
                ]);
            }
        }
    }

    protected function seedAmenities(): void
    {
        $amenities = [
            [
                'name' => 'Nizuc Resort & Spa - Virtuoso Amenities',
                'description' => 'Upgrade on arrival, subject to availability\n Daily Buffet breakfast for up to two guests per bedroom, served in Café de la Playa\n Complimentary arrival, one-way private airport transfers\n Complimentary hydrothermal therapy session at NIZUC Spa by ESPA for up to two guests, once during stay\n Early Check-In / Late Check-Out, subject to availability\n Complimentary Wi-Fi',
            ],
            [
                'name' => 'The Cove Atlantis - Virtuoso',
                'description' => 'Upgrade on arrival, subject to availability\nDaily breakfast credit of $50 per person, for up to two guests per bedroom, served via in-room dining (credit is non-cumulative)\n$100USD equivalent Resort or Hotel credit utilized during stay (not combinable, not valid on room rate, no cash value if not redeemed in full)\nEarly Check-In / Late Check-Out, subject to availability\nComplimentary Wi-Fi',
            ],
            [
                'name' => 'Nizuc Signature Amenities',
                'description' => 'Buffet Breakfast for two daily at Café de la Playa\n $100 USD Spa Services Credit, per room, once per stay (Certain restrictions apply, not valid for Beauty Salon, Spa products and Fitness and Wellness services)\n Hydrotherapy Experience for two at Spa once per stay\n Welcome Amenity\n The following amenities are subject to availability at the time of check-in/departure:\n Upgrade\n Early Check-In\n Late Check-Out\n SUITE/VILLA PRIVILEGES\n Combinable with Exclusive Amenities listed above.\nA two night minimum stay applies for Suite/Villa Privileges.\n\nUS$100 Food and Beverage credit, once per stay',
            ],
            [
                'name' => 'The Cove Atlantis - Signature',
                'description' => 'Full American Breakfast for two daily at any Restaurant or Room Service (Inclusive of gratuities) $90 daily value.\n$100 Resort Credit, once during stay\nThe following amenities are subject to availability at the time of check-in/departure:\nUpgrade from Cove - Ocean King to Deluxe Ocean King\nEarly Check-In\nLate Check-Out\nSUITE/VILLA PRIVILEGES\nCombinable with Exclusive Amenities listed above.\nA two night minimum stay applies for Suite/Villa Privileges.\n\nUS$100 Food and Beverage credit, once per stay',
            ],
        ];

        foreach ($amenities as $amenity) {
            ConfigAmenity::updateOrCreate([
                'name' => $amenity['name'],
            ], [
                'name' => $amenity['name'],
                'description' => $amenity['description'],
            ]);
        }
    }

    protected function seedAffiliationsAndAmenities(): void
    {
        $hotels = Hotel::whereIn('giata_code', $this->hotelCodes)->get();
        $products = Product::whereIn('related_id', $hotels->pluck('id'))->get();

        $amenities = ConfigAmenity::whereIn('name', [
            'Nizuc Resort & Spa - Virtuoso Amenities',
            'The Cove Atlantis - Virtuoso',
            'Nizuc Signature Amenities',
            'The Cove Atlantis - Signature'
        ])->get()->keyBy('name');

        foreach ($products as $product) {
            $affiliation = ProductAffiliation::updateOrCreate([
                'product_id' => $product->id,
            ], [
                'start_date' => now(),
            ]);

            // Virtuoso amenities (Nizuc + The Cove)
            $virtuosoAmenities = [
                $amenities['Nizuc Resort & Spa - Virtuoso Amenities'] ?? null,
                $amenities['The Cove Atlantis - Virtuoso'] ?? null,
            ];

            // Signature amenities (Nizuc + The Cove)
            $signatureAmenities = [
                $amenities['Nizuc Signature Amenities'] ?? null,
                $amenities['The Cove Atlantis - Signature'] ?? null,
            ];

            foreach (array_filter($virtuosoAmenities) as $amenity) {
                ProductAffiliationAmenity::updateOrCreate([
                    'product_affiliation_id' => $affiliation->id,
                    'amenity_id' => $amenity->id,
                ], [
                    'consortia' => ['Virtuoso'],
                    'is_paid' => true,
                    'price' => 0,
                    'apply_type' => 'per_room',
                ]);
            }

            foreach (array_filter($signatureAmenities) as $amenity) {
                ProductAffiliationAmenity::updateOrCreate([
                    'product_affiliation_id' => $affiliation->id,
                    'amenity_id' => $amenity->id,
                ], [
                    'consortia' => ['Signature'],
                    'is_paid' => false,
                    'price' => 0,
                    'apply_type' => 'per_room',
                ]);
            }
        }
    }
}
