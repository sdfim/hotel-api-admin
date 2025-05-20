<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigAmenity;
use App\Models\Mapping;
use App\Models\PricingRule;
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
    protected const NIZUC = 'Nizuc Resort & Spa Test';
    protected const GARZA = 'Garza Blanca Cancun Test';
    protected const GRAN_VELAS = 'Grand Velas Riviera Maya Test';
    protected const BANYAN_TREE = 'Banyan Tree Mayakoba Test';
    protected const SUGAR_BEACH = 'Sugar Beach Test';
    protected const COVE_ATLANTIS = 'The Cove Atlantis Test';
    protected const ROYAL_ATLANTIS = 'The Royal Atlantis Test';
    protected const REEF_ATLANTIS = 'The Reef Atlantis Test';


    protected array $hotelCodes = [];

    public function run(): void
    {
        $this->hotelCodes = $this->seedProperties();

        $vendor = $this->seedVendor();

        $this->seedProductsAndDeposits($vendor);
        $this->seedAmenities();
        $this->seedAffiliationsAndAmenities();
        $this->seedPricingRule();
    }

    public function seedProperties(): array
    {
        $hotels = [
            // Cancun
            [
                'code' => 1004,
                'name' => self::NIZUC,
                'city' => 'Cancun',
                'city_id' => 508,
                'locale' => 'Yucatan Peninsula',
                'locale_id' => 1026,
                'giata_id' => 1004,
                'supplier_id' => 51721,
                'latitude' => 21.033381,
                'longitude' => -86.787406,
                'country_code' => 'MX',
            ],
            [
                'code' => 1005,
                'name' => self::GARZA,
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
                'name' => self::GRAN_VELAS,
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
                'name' => self::BANYAN_TREE,
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
                'name' => self::SUGAR_BEACH,
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
                'name' => self::COVE_ATLANTIS,
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
                'name' => self::ROYAL_ATLANTIS,
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
                'name' => self::REEF_ATLANTIS,
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

            $newHotel = Hotel::updateOrCreate([
                'giata_code' => $hotel['code'],
            ], [
                'star_rating' => 5,
                'num_rooms' => 100,
                'address' => $property->address,
                'featured_flag' => 1,
                'sale_type' => HotelSaleTypeEnum::DIRECT_CONNECTION->value,
                'travel_agent_commission' => 10.0,
                'hotel_board_basis' => ['Breakfast Included'],
                'room_images_source_id' => 1,
            ]);

            HotelRoom::where('hotel_id', $newHotel->id)->delete();

            $rooms = $this->getHotelRooms($hotel['name'], $newHotel);

            foreach ($rooms as $room) {
                HotelRoom::create($room);
            }
        }

        return collect($hotels)->mapWithKeys(fn($h) => [$h['code'] => $h['name']])->toArray();
    }

    protected function getHotelRooms(string $origin, Hotel $hotel): array
    {
        $config = [
            self::NIZUC => [
                ['Double', 'Room, 2 Queen Beds, Garden View (Double)', '79 sqm', ['1 Queen Beds', 'Double Bed'], ['Garden View'], '5531551'],
                ['Double', 'Deluxe Room, 2 Queen Beds, Ocean View (Double)', '75 sqm', ['2 Queen Beds'], ['Ocean View'], '5531551'],
                ['Suite', 'Deluxe Room, 1 King Bed, Ocean View', '79 sqm', ['1 King Bed'], ['Ocean View'], '5531551'],
                ['Suite', 'Suite (Ocean, Adults Only)', '88 sqm', ['1 King Bed'], ['Ocean View', 'Garden View'], '5531551'],
            ],
            self::GARZA => [
                ['Suite', 'Suite, 1 Bedroom, Oceanfront', '144 sqm', ['1 King Bed', '1 Double Sofa'], ['Ocean View'], '2614708'],
                ['Suite', 'Two Bedroom Suite Panoramic', '300 sqm', ['2 King Beds', '2 Full Beds'], ['Ocean View'], '2614708'],
                ['Suite', 'Junior Suite, 1 King Bed, Ocean View', '300 sqm', ['1 King Bed'], ['Ocean View'], '2614708'],
                ['Double', 'Family 2 Bedroom Panoramic Suite', '300 sqm', ['1 King Bed', '2 Queen Beds', '1 Double Sofa Bed'], ['Ocean View'], '2614708'],
                ['Double', 'Honeymoon Room', '123 sqm', ['1 King Bed'], ['Partial ocean view'], '2614708'],
            ],
            self::GRAN_VELAS => [
                ['Double', 'Nature View Suite - Zen Experience', '110 sqm', ['1 King Bed'], ['Resort View'], '2406344'],
                ['Suite', 'Ambassador Suite Ocean View', '118 sqm', ['1 King Bed'], ['Ocean View'], '2406344'],
                ['Suite', 'Grand Class Pool Suite Ocean Front', '128 sqm', ['1 King Bed'], ['Ocean View'], '2406344'],
                ['Double', 'Zen Grand Two Bedroom Family Suite Nature View', '220 sqm', ['1 King Bed', '2 Queen Beds'], ['Resort View'], '2406344'],
            ],
            self::BANYAN_TREE => [
                ['Double', 'Bliss Pool Villa', '293 sqm', ['1 King Bed'], ['Canal view'], '2393134'],
                ['Suite', 'Oceanfront Veranda Pool Suite - King', '162 sqm', ['1 King Bed'], ['Beach view'], '2393134'],
                ['Suite', 'Beachfront Terrace Pool Suite', '162 sqm', ['1 King Bed'], ['Beach view'], '2393134'],
                ['Suite', 'Wellbeing Sanctuary Pool Villa - King', '322 sqm', ['1 King Bed'], ['Canal view'], '2393134'],
                ['Suite', 'Lagoon & Sunset Rooftop Pool Villa', '222 sqm', ['1 King Bed'], ['Lagoon view'], '2393134'],
            ],
            self::SUGAR_BEACH => [
                ['STD', 'STD Loyalty', '293 sqm', ['1 King Bed'], ['Canal view'], '2393134'],
                ['Luxury', ' Luxury', '299 sqm', ['1 King Bed'], ['Beach view'], '2393134'],
                ['Suite', 'Suite', '162 sqm', ['1 King Bed'], ['Beach view'], '2393134'],
                ['Suite', 'Suite', '322 sqm', ['1 King Bed'], ['Canal view'], '2393134'],
            ],
            self::COVE_ATLANTIS => [
                ['Suite', 'Ocean Suite - 1 King Bed', '120 sqm', ['1 King Bed'], ['Ocean View'], '9876541'],
                ['Double', 'Deluxe Room - 2 Queen Beds', '100 sqm', ['2 Queen Beds'], ['Harbor View'], '9876542'],
                ['Suite', 'Penthouse Suite - Oceanfront', '200 sqm', ['1 King Bed', '1 Sofa Bed'], ['Oceanfront'], '9876543'],
                ['Double', 'Luxury Room - Water Park Access', '105 sqm', ['1 King Bed'], ['Resort View'], '9876544'],
                ['Suite', 'Family Suite - Balcony', '150 sqm', ['2 Queen Beds', '1 Sofa Bed'], ['Balcony View'], '9876545'],
            ],
            self::ROYAL_ATLANTIS => [
                ['Double', 'Royal Tower Room - Water View', '110 sqm', ['2 Queen Beds'], ['Water View'], '8765431'],
                ['Suite', 'Regal Suite - 1 King Bed', '150 sqm', ['1 King Bed'], ['Ocean View'], '8765432'],
                ['Suite', 'Presidential Suite - Panoramic View', '250 sqm', ['1 King Bed', '2 Twin Beds'], ['Panoramic Ocean View'], '8765433'],
                ['Double', 'Deluxe Room - Resort View', '95 sqm', ['2 Double Beds'], ['Resort View'], '8765434'],
                ['Suite', 'Grand Royal Family Suite', '200 sqm', ['2 King Beds'], ['Ocean & Resort View'], '8765435'],
            ],
            self::REEF_ATLANTIS => [
                ['Studio', 'Studio Suite - Kitchenette', '90 sqm', ['1 King Bed'], ['Resort View'], '7654321'],
                ['Suite', '1 Bedroom Terrace Suite', '130 sqm', ['1 King Bed', '1 Sofa Bed'], ['Terrace View'], '7654322'],
                ['Double', 'Deluxe Room - Ocean View', '95 sqm', ['2 Double Beds'], ['Ocean View'], '7654323'],
                ['Suite', 'Reef Club Suite - Full Kitchen', '140 sqm', ['1 King Bed'], ['Marina View'], '7654324'],
                ['Double', 'Reef Family Room - 2 Queen Beds', '115 sqm', ['2 Queen Beds'], ['Ocean/Pool View'], '7654325'],
            ],
        ];

        return collect($config[$origin] ?? [])
            ->map(fn($data) => $this->makeRoom($hotel, $origin, ...$data))
            ->toArray();
    }

    protected function makeRoom(Hotel $hotel, string $name, string $externalCode, string $description, string $area, array $bedGroups, array $views, string $supplierCode): array
    {
        return [
            'hotel_id' => $hotel->id,
            'external_code' => $externalCode,
            'name' => $name,
            'supplier_codes' => json_encode([
                [
                    'code' => $supplierCode,
                    'supplier' => 'Expedia',
                ]
            ]),
            'description' => $description,
            'area' => $area,
            'bed_groups' => $bedGroups,
            'room_views' => $views,
        ];
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
        $hotelNamesByCode = $this->hotelCodes;

        $hotels = Hotel::whereIn('giata_code', array_keys($hotelNamesByCode))->get();
        $hotelCodes = array_keys($hotelNamesByCode);
        shuffle($hotelCodes);

        $firstTwo = array_slice($hotelCodes, 0, 2);
        $third = $hotelCodes[2] ?? null;

        foreach ($hotels as $hotel) {
            $hotelName = $hotelNamesByCode[$hotel->giata_code] ?? 'Unknown Hotel';

            $product = Product::updateOrCreate([
                'related_id' => $hotel->id,
                'related_type' => Hotel::class,
            ], [
                'vendor_id' => $vendor->id,
                'product_type' => ProductTypeEnum::HOTEL->value,
                'name' => $hotelName,
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
            'Upgrade on arrival, subject to availability',
            'Daily Buffet breakfast for up to two guests per bedroom, served in Café de la Playa',
            'Complimentary arrival, one-way private airport transfers',
            'Complimentary hydrothermal therapy session at NIZUC Spa by ESPA for up to two guests, once during stay',
            'Early Check-In / Late Check-Out, subject to availability',
            'Complimentary Wi-Fi',
            'Buffet Breakfast for two daily at Café de la Playa',
            '$100 USD Spa Services Credit, per room, once per stay (Certain restrictions apply, not valid for Beauty Salon, Spa products and Fitness and Wellness services)',
            'Hydrotherapy Experience for two at Spa once per stay',
            'Welcome Amenity ',
            'The following amenities are subject to availability at the time of check-in/departure:',
            'Upgrade',
            'Early Check-In',
            'Late Check-Out',
            'SUITE/VILLA PRIVILEGES',
            'A two night minimum stay applies for Suite/Villa Privileges.',
            'US$100 Food and Beverage credit, once per stay',
            'Daily breakfast credit of $50 per person, for up to two guests per bedroom, served via in-room dining (credit is non-cumulative)',
            '$100USD equivalent Resort or Hotel credit utilized during stay (not combinable, not valid on room rate, no cash value if not redeemed in full)',
            'Full American Breakfast for two daily at any Restaurant or Room Service (Inclusive of gratuities) $90 daily value.',
            '$100 Resort Credit, once during stay',
            'Upgrade from Cove - Ocean King to Deluxe Ocean King',
            'Combinable with Exclusive Amenities listed above.',
        ];

        foreach ($amenities as $amenity) {
            ConfigAmenity::updateOrCreate([
                'name' => $amenity,
            ], [
                'name' => $amenity,
                'description' => $amenity,
            ]);
        }
    }

    protected function seedAffiliationsAndAmenities(): void
    {
        $hotelCodes = array_keys($this->hotelCodes);
        $hotels = Hotel::whereIn('giata_code', $hotelCodes)->get();
        $products = Product::whereIn('related_id', $hotels->pluck('id'))->get();

        $amenityNames = [
            'Upgrade on arrival, subject to availability',
            'Daily Buffet breakfast for up to two guests per bedroom, served in Café de la Playa',
            'Complimentary arrival, one-way private airport transfers',
            'Complimentary hydrothermal therapy session at NIZUC Spa by ESPA for up to two guests, once during stay',
            'Early Check-In / Late Check-Out, subject to availability',
            'Complimentary Wi-Fi',
            'Buffet Breakfast for two daily at Café de la Playa',
            '$100 USD Spa Services Credit, per room, once per stay (Certain restrictions apply, not valid for Beauty Salon, Spa products and Fitness and Wellness services)',
            'Hydrotherapy Experience for two at Spa once per stay',
            'Welcome Amenity ',
            'The following amenities are subject to availability at the time of check-in/departure:',
            'Upgrade',
            'Early Check-In',
            'Late Check-Out',
            'SUITE/VILLA PRIVILEGES',
            'A two night minimum stay applies for Suite/Villa Privileges.',
            'US$100 Food and Beverage credit, once per stay',
            'Daily breakfast credit of $50 per person, for up to two guests per bedroom, served via in-room dining (credit is non-cumulative)',
            '$100USD equivalent Resort or Hotel credit utilized during stay (not combinable, not valid on room rate, no cash value if not redeemed in full)',
            'Full American Breakfast for two daily at any Restaurant or Room Service (Inclusive of gratuities) $90 daily value.',
            '$100 Resort Credit, once during stay',
            'Upgrade from Cove - Ocean King to Deluxe Ocean King',
            'Combinable with Exclusive Amenities listed above.',
        ];

        $amenities = ConfigAmenity::whereIn('name', $amenityNames)->get()->keyBy('name');

        // Map consortia configurations
        $consortiaAmenityConfig = [
            'Virtuoso_paid' => [
                'Upgrade on arrival, subject to availability',
                'Daily Buffet breakfast for up to two guests per bedroom, served in Café de la Playa',
                'Complimentary arrival, one-way private airport transfers',
                'Complimentary hydrothermal therapy session at NIZUC Spa by ESPA for up to two guests, once during stay',
                'Early Check-In / Late Check-Out, subject to availability',
                'Complimentary Wi-Fi',
            ],
            'Virtuoso_free' => [
                'Upgrade on arrival, subject to availability',
                'Daily Buffet breakfast for up to two guests per bedroom, served in Café de la Playa',
                '$100 USD Spa Services Credit, per room, once per stay (Certain restrictions apply, not valid for Beauty Salon, Spa products and Fitness and Wellness services)',
                'Early Check-In / Late Check-Out, subject to availability',
                'Complimentary Wi-Fi',
            ],
            'Signature_nizuc' => [
                'Buffet Breakfast for two daily at Café de la Playa',
                '$100 USD Spa Services Credit, per room, once per stay (Certain restrictions apply, not valid for Beauty Salon, Spa products and Fitness and Wellness services)',
                'Hydrotherapy Experience for two at Spa once per stay',
                'Welcome Amenity ',
                'The following amenities are subject to availability at the time of check-in/departure:',
                'Upgrade',
                'Early Check-In',
                'Late Check-Out',
                'SUITE/VILLA PRIVILEGES',
                'Combinable with Exclusive Amenities listed above.',
                'A two night minimum stay applies for Suite/Villa Privileges.',
                'US$100 Food and Beverage credit, once per stay',
            ],
            'Signature_cove' => [
                'Full American Breakfast for two daily at any Restaurant or Room Service (Inclusive of gratuities) $90 daily value.',
                '$100 Resort Credit, once during stay',
                'Upgrade from Cove - Ocean King to Deluxe Ocean King',
                'Early Check-In',
                'Late Check-Out',
                'SUITE/VILLA PRIVILEGES',
                'Combinable with Exclusive Amenities listed above.',
                'A two night minimum stay applies for Suite/Villa Privileges.',
                'US$100 Food and Beverage credit, once per stay',
            ],
        ];

        foreach ($products as $product) {
            $affiliation = ProductAffiliation::updateOrCreate([
                'product_id' => $product->id,
            ], [
                'start_date' => now(),
            ]);

            $availableAmenityNames = array_unique(array_merge(
                $consortiaAmenityConfig['Virtuoso_paid'],
                $consortiaAmenityConfig['Virtuoso_free'],
                $consortiaAmenityConfig['Signature_nizuc'],
                $consortiaAmenityConfig['Signature_cove'],
            ));

            $selectedNames = collect($availableAmenityNames)
                ->shuffle()
                ->take(rand(4, 10));

            $this->attachAmenities($affiliation, $amenities, $selectedNames->all());
        }
    }

    protected function attachAmenities(
        ProductAffiliation $affiliation,
        \Illuminate\Support\Collection $amenities,
        array $names,
    ): void {
        foreach ($names as $name) {
            $amenity = $amenities->get($name);
            if (! $amenity) {
                continue;
            }

            $consortia = ['Virtuoso', 'Signature'][rand(0, 1)];
            $isPaid = (bool) rand(0, 1);

            ProductAffiliationAmenity::updateOrCreate([
                'product_affiliation_id' => $affiliation->id,
                'amenity_id' => $amenity->id,
            ], [
                'consortia' => [$consortia],
                'is_paid' => $isPaid,
                'price' => 0,
                'apply_type' => 'per_room',
            ]);
        }
    }

    protected function seedPricingRule(): void
    {
        $hotelCodes = array_keys($this->hotelCodes);
        $hotels = Hotel::whereIn('giata_code', $hotelCodes)->get()->keyBy('giata_code');
        $products = Product::whereIn('related_id', $hotels->pluck('id'))->get()->keyBy('related_id');

        $priceValueTargets = ['per_room', 'per_person', 'per_night', 'per_night_per_person', 'not_applicable'];
        $margins = [18, 20, 25];

        foreach ($hotels as $hotel) {
            $product = $products[$hotel->id] ?? null;

            if (! $product) {
                continue;
            }

            $margin = $margins[array_rand($margins)];
            $manipulablePriceType = ['total_price', 'net_price'][rand(0, 1)];
            $priceValueType = ['percentage', 'fixed_value'][rand(0, 1)];

            $rule = PricingRule::create([
                'name' => "Margin {$margin}",
                'is_sr_creator' => true,
                'weight' => 10,
                'is_exclude_action' => false,
                'manipulable_price_type' => $manipulablePriceType,
                'price_value_type' => $priceValueType,
                'price_value' => $margin,
                'price_value_target' => $priceValueTargets[array_rand($priceValueTargets)],
                'rule_start_date' => now(),
                'rule_expiration_date' => now()->addYears(10),
            ]);

            $rule->conditions()->create([
                'field' => 'property',
                'value_from' => $hotel->giata_code,
                'compare' => '=',
            ]);
            $rule->conditions()->create([
                'field' => 'supplier',
                'value_from' => 2, // HBSI
                'compare' => '=',
            ]);
            $rule->conditions()->create([
                'field' => 'travel_date',
                'value_from' => '2025-01-01',
                'value_to' => '2027-12-31',
                'compare' => 'between',
            ]);
        }
    }
}
