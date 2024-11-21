<?php

namespace App\Console\Commands\HotelContentRepository;

use App\Console\Commands\BaseTrait;
use App\Models\ExpediaContent;
use App\Models\Mapping;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Faker\Factory as Faker;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\FeeTaxTypeEnum;
use Modules\Enums\FeeTaxValueTypeEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use Modules\HotelContentRepository\Models\ProductAttribute;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;
use Modules\HotelContentRepository\Models\ProductFeeTax;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\KeyMappingOwner;
use Modules\HotelContentRepository\Models\Vendor;

class TestSeederExpediaContentToHotels extends Command
{
    use BaseTrait;

    protected $signature = 'transform:expedia-to-hotels';
    protected $description = 'Transform ExpediaContent records to Hotel related models';

    protected $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Faker::create();
    }

    public function handle()
    {
        $st = microtime(true);
        $existingHotels = KeyMapping::whereHas('keyMappingOwner', function ($query) {
                $query->where('name', 'GIATA');
            })
            ->join('mappings', 'pd_key_mapping.key_id', '=', 'mappings.giata_id')
            ->distinct('mappings.supplier_id')
            ->pluck('mappings.supplier_id');

        $expediaContents = ExpediaContent::has('expediaSlave')
            ->whereNotIn('property_id', $existingHotels)
            ->cursor();

        $this->st = microtime(true);

        if (Vendor::count() === 0) {
            // Create vendors using VendorFactory
            Vendor::factory()->count(10)->create();
        }

        foreach ($expediaContents as $k => $expediaContent) {
            $this->warn('Start '. $k . ' Transform ExpediaContent ' . $expediaContent->property_id . ' to Hotel.');

            $this->transformAndSave($expediaContent);

            $this->info('End Transform ExpediaContent ' . $this->runtime() . ' seconds.');

            if ($k > 3) break;
        }

        $this->info('Transformation completed successfully. ' . (microtime(true) - $st) . ' seconds.');
    }

    private function transformAndSave($expediaContent)
    {
        $expediaId = ContentSource::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;

        $address = $expediaContent->address;
        unset($address['localized']);

        $hotelData = [
            'weight' =>  rand(1, 100),
            'sale_type' => 'Direct Connection',
            'address' => $address,
            'star_rating' => $expediaContent?->rating,
            'num_rooms' => Arr::get($expediaContent->expediaSlave->statistics, '52.value'),
            'room_images_source_id' => $expediaId,
            'product_board_basis' => '',
        ];

        $hotel = Hotel::updateOrCreate(
            ['address' => $address],
            $hotelData
        );

        $productData = [
            'vendor_id' => Vendor::inRandomOrder()->value('id'),
            'product_type' => 'hotel',
            'name' => $expediaContent->name,
            'verified' => true,
            'content_source_id' => $expediaId,
            'property_images_source_id' => $expediaId,
            'lat' => Arr::get($expediaContent?->location, 'coordinates.latitude'),
            'lng' => Arr::get($expediaContent?->location, 'coordinates.longitude'),
            'default_currency' => Arr::get($expediaContent->expediaSlave->onsite_payments, 'currency', 'USD'),
            'website' => '',
            'location_gm' => Arr::get($expediaContent?->location, 'coordinates'),
            'related_id' => $hotel->id,
            'related_type' => Hotel::class,
        ];

        $product = Product::updateOrCreate(
            ['name' => $expediaContent->name, 'related_id' => $hotel->id],
            $productData
        );

        $this->updateOrCreateProductAffiliation($product);
        $this->updateOrCreateHotelImages($expediaContent, $product);
        $this->updateOrCreateKey($expediaContent, $product);
        $this->updateOrFeeTaxs($expediaContent, $product);

        $this->updateOrRooms($expediaContent, $hotel);
    }

    private function updateOrCreateProductAffiliation(Product $product): void
    {
        ProductAffiliation::updateOrCreate(
            ['product_id' => $product->id],
            [
                'product_id' => $product->id,
                'affiliation_name' => 'UJV Exclusive Amenities',
                'combinable' => true,
            ]
        );
    }

    private function parseFees($html, $feeCategory) {
        preg_match_all('/<li>(.*?)<\/li>/', $html, $matches);
        $fees = [];
        foreach ($matches[1] as $match) {
            // Expanded regex to capture types even when additional info like "subject to availability" exists
            preg_match('/(.*?):\s*(?:approximately\s*)?USD\s*([\d.]+)(?:–([\d.]+))?\s*(per person|per day|per accommodation, per stay|per night)?(?:.*subject to availability)?/', $match, $feeDetails);
            if ($feeDetails) {
                $type = $feeDetails[4] ?? 'unknown';
                // Handle cases like "subject to availability"
                if (empty($type) || str_contains($match, 'subject to availability')) {
                    $type = 'subject to availability';
                }
                $possibleTypes = ['per person', 'per day', 'per accommodation, per stay', 'per night', 'subject to availability'];
                foreach ($possibleTypes as $possibleType) {
                    if (str_contains($match, $possibleType)) {
                        $type = $possibleType;
                        break;
                    }
                }

                $fees[] = [
                    'name' => $feeDetails[1],
                    'net_value' => $feeDetails[2],
                    'rack_value' => $feeDetails[2],
                    'tax' => 0, // Assuming tax is not provided
                    'type' => $type,
                    'fee_category' => $feeCategory,
                ];
            }
        }
        return $fees;
    }

    private function updateOrFeeTaxs(ExpediaContent $expediaContent, Product $product): void
    {
        $hotelId = $product->id;
        $data = $expediaContent->expediaSlave->fees;

        $optional = Arr::get($data, 'optional', '');
        $optionalFees = $this->parseFees($optional, 'optional');
        $mandatory = Arr::get($data, 'mandatory', '');
        $mandatoryFees = $this->parseFees($mandatory, 'mandatory');

        $fees = array_merge($optionalFees, $mandatoryFees);

        foreach ($fees as $fee) {
            ProductFeeTax::updateOrCreate(
                ['product_id' => $hotelId, 'name' => $fee['name']],
                [
                    'product_id' => $hotelId,
                    'name' => $fee['name'],
                    'fee_category' => $fee['fee_category'],
                    'net_value' => $fee['net_value'],
                    'rack_value' => $fee['rack_value'],
                    'commissionable' => false,
                    'type' => $this->faker->randomElement([
                        FeeTaxTypeEnum::TAX->value,
                        FeeTaxTypeEnum::FEE->value
                    ]),
                    'value_type' => $this->faker->randomElement([
                        FeeTaxValueTypeEnum::PERCENTAGE->value,
                        FeeTaxValueTypeEnum::AMOUNT->value
                    ]),
                    'collected_by' => $this->faker->randomElement([
                        FeeTaxCollectedByEnum::DIRECT->value,
                        FeeTaxCollectedByEnum::VENDOR->value
                    ]),                ]
            );
        }
    }

    private function updateOrDescriptiveContent(ExpediaContent $expediaContent, Product $product): void
    {
        $descriptions = $expediaContent->expediaSlave->descriptions;

        foreach ($descriptions as $sectionName => $description) {
            $section = ProductDescriptiveContentSection::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'section_name' => $sectionName,
                ],
                [
                    'start_date' => null, // Assuming no start_date provided
                    'end_date' => null, // Assuming no end_date provided
                ]
            );

            $sectionId = $section->id;

            ProductDescriptiveContent::updateOrCreate(
                [
                    'content_sections_id' => $sectionId,
                    'section_name' => $sectionName,
                ],
                [
                    'meta_description' => null, // Assuming no meta_description provided
                    'property_description' => $description,
                    'cancellation_policy' => null, // Assuming no cancellation_policy provided
                    'pet_policy' => null, // Assuming no pet_policy provided
                    'terms_conditions' => null, // Assuming no terms_conditions provided
                    'fees_paid_at_hotel' => null, // Assuming no fees_paid_at_hotel provided
                    'staff_contact_info' => null, // Assuming no staff_contact_info provided
                    'validity_start' => null, // Assuming no validity_start provided
                    'validity_end' => null, // Assuming no validity_end provided
                ]
            );
        }
    }
    private function updateOrRooms(ExpediaContent $expediaContent, Hotel $hotel): void
    {
        $sectionId = ImageSection::where('name', 'room')->value('id');

        $rooms = $expediaContent->expediaSlave->rooms;
        foreach ($rooms as $room) {
            $name = Arr::get($room, 'name', 'No name');

            $description = Arr::get($room, 'descriptions.overview', 'No description');
            $cleanDescription = strip_tags($description);
            $cleanDescription = preg_replace('/\s+/', ' ', $cleanDescription);

            $roomData = [
                'hotel_id' => $hotel->id,
                // TODO: need special tools to create a map
                'hbsi_data_mapped_name' => '',
                'name' => $name,
                'description' => $cleanDescription,
//                'amenities' => array_values(Arr::get($room, 'amenities', [])),
//                'occupancy' => Arr::get($room, 'occupancy', 'No occupancy'),
//                'bed_groups' => array_values(Arr::get($room, 'bed_groups', [])),
            ];

            $hotelRoom = HotelRoom::updateOrCreate(
                ['hotel_id' => $hotel->id, 'name' => $name],
                $roomData
            );

            $roomGallery = ImageGallery::firstOrCreate(
                ['gallery_name' => 'Room Gallery ' . $name . ' room code ' . Arr::get($room, 'id')],
                ['description' => 'Gallery for room ' . $name . ' in hotel ' . $hotel?->name]
            );

            $imageIds = [];
            foreach (Arr::get($room, 'images', []) as $image) {
                $tag = Arr::get($image, 'caption', 'No caption');
                foreach ($image['links'] as $size => $link) {
                    $hotelImage = Image::firstOrNew(['image_url' => $link['href']]);
                    $hotelImage->tag = $tag;
                    $hotelImage->weight = $size;
                    $hotelImage->section_id = $sectionId;
                    $hotelImage->save();
                    $imageIds[] = $hotelImage->id;
                }
            }

            $roomGallery->images()->syncWithoutDetaching($imageIds);

            $hotelRoom->galleries()->syncWithoutDetaching([$roomGallery->id]);
        }
    }

    private function updateOrCreateKey(ExpediaContent $expediaContent, Product $product): void
    {
        $key_id = Mapping::where('supplier_id', $expediaContent->property_id)
            ->where('supplier', SupplierNameEnum::EXPEDIA->value)
            ->value('giata_id');

        if (!$key_id) return;

        $keyMapping = [
            'product_id' => $product->id,
            'key_id' => $key_id,
            'key_mapping_owner_id' => KeyMappingOwner::where('name', 'GIATA')->value('id'),
        ];
        KeyMapping::updateOrCreate(
            ['product_id' => $product->id],
            $keyMapping
        );
    }

    private function updateOrCreateHotelAttributes(ExpediaContent $expediaContent, Product $product): void
    {
        $flattenedAttributes = [];
        foreach ($expediaContent->expediaSlave->attributes as $category => $items) {
            foreach ($items as $item) {
                $flattenedAttributes[] = [
                    'product_id' => $product->id,
                    'name' => $category,
                    'attribute_value' => $item['name'],
                ];
            }
        }
        foreach ($flattenedAttributes as $attribute) {
            ProductAttribute::updateOrCreate(
                ['product_id' => $attribute['product_id'], 'attribute_value' => $attribute['name']],
                $attribute
            );
        }
    }

    private function updateOrCreateHotelImages(ExpediaContent $expediaContent, Product $product): void
    {
        $imageGallery = ImageGallery::firstOrCreate(
            ['gallery_name' => 'Expedia Gallery Hotel ' . $product->name],
            ['description' => 'Gallery for Expedia images for hotel ' . $product->name]
        );

        $sectionId = ImageSection::where('name', 'hotel')->value('id');

        $upsertedImages = [];
        foreach ($expediaContent->expediaSlave->images as $image) {
            $tag = Arr::get($image, 'caption', 'No caption');
            foreach ($image['links'] as $weight => $data) {
                $hotelImage = Image::firstOrNew(['image_url' => $data['href'], 'section_id' => $sectionId]);
                $hotelImage->tag = $tag;
                $hotelImage->weight = $weight;
                $hotelImage->section_id = $sectionId;
                $hotelImage->save();
                $upsertedImages[] = $hotelImage->id;
            }
        }

        $imageGallery->images()->syncWithoutDetaching($upsertedImages);
        // Step 5: Attach the ImageGallery to the Hotel
        $product->galleries()->syncWithoutDetaching([$imageGallery->id]);
    }
}
