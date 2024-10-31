<?php

namespace App\Console\Commands\HotelContentRepository;

use App\Console\Commands\BaseTrait;
use App\Models\ExpediaContent;
use App\Models\Mapping;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAffiliation;
use Modules\HotelContentRepository\Models\HotelAttribute;
use Modules\HotelContentRepository\Models\HotelDescriptiveContent;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;
use Modules\HotelContentRepository\Models\HotelFeeTax;
use Modules\HotelContentRepository\Models\HotelImage;
use Modules\HotelContentRepository\Models\HotelImageSection;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class TestSeederExpediaContentToHotels extends Command
{
    use BaseTrait;

    protected $signature = 'transform:expedia-to-hotels';
    protected $description = 'Transform ExpediaContent records to Hotel related models';

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
            'name' => $expediaContent->name,
            // Direct Connection, Manual Contract, Commission Tracking (No Rules Apply)
            'type' => 'Direct Connection',

            'verified' => true,
            'direct_connection' => true,
            'manual_contract' => true,
            'commission_tracking' => true,
            'featured' => false,
            'channel_management' => true,

            'address' => $address,
            'location' => Arr::get($expediaContent?->location, 'coordinates'),
            'star_rating' => $expediaContent?->rating,

            'website' => '',
            'num_rooms' => Arr::get($expediaContent->expediaSlave->statistics, '52.value'),

            'content_source_id' => $expediaId,
            'room_images_source_id' => $expediaId,
            'property_images_source_id' => $expediaId,

            'hotel_board_basis' => '',
            'default_currency' => Arr::get($expediaContent->expediaSlave->onsite_payments, 'currency'),
        ];

        $hotel = Hotel::updateOrCreate(
            ['name' => $expediaContent->name],
            $hotelData);

        $this->updateOrCreateHotelAffiliation($hotel);
//        $this->updateOrCreateHotelAttributes($expediaContent, $hotel);
        $this->updateOrCreateHotelImages($expediaContent, $hotel);
        $this->updateOrCreateKey($expediaContent, $hotel);
        $this->updateOrRooms($expediaContent, $hotel);
        $this->updateOrFeeTaxs($expediaContent, $hotel);
//        $this->updateOrDescriptiveContent($expediaContent, $hotel);

        // HotelDescriptiveContent::create([...]);
        // HotelDescriptiveContentSection::create([...]);

        // TODO: Promotions
        //Name
        //Description
        //Imagery
        //Validity Range
        //Booking Range
        //Terms and Conditions
        //Any reasons for exclusion
        // HotelPromotion::create([...]);

        // TODO: Informative Services Section (Add on Module)
        //Section to specify the services that can be booked at the hotel by the concierge
        //This will be selected from Informational Services module
        //Ability to change the cost of the Service at this level will be available
        //Returned in the Hotel Content call
        // HotelInformativeService::create([...]);

        // TODO: This will be rule based and is a new area for the system to be able to create on the OBE
        //Consortia
        //Room Type
        //Date Range option
        // TravelAgencyCommission::create([...]);
    }

    private function updateOrCreateHotelAffiliation(Hotel $hotel): void
    {
        HotelAffiliation::updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'hotel_id' => $hotel->id,
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

    private function updateOrFeeTaxs(ExpediaContent $expediaContent, Hotel $hotel): void
    {
        $hotelId = $hotel->id;
        $data = $expediaContent->expediaSlave->fees;

        $optional = Arr::get($data, 'optional', '');
        $optionalFees = $this->parseFees($optional, 'optional');
        $mandatory = Arr::get($data, 'mandatory', '');
        $mandatoryFees = $this->parseFees($mandatory, 'mandatory');

        $fees = array_merge($optionalFees, $mandatoryFees);

        foreach ($fees as $fee) {
            HotelFeeTax::updateOrCreate(
                ['hotel_id' => $hotelId, 'name' => $fee['name']],
                [
                    'hotel_id' => $hotelId,
                    'name' => $fee['name'],
                    'fee_category' => $fee['fee_category'],
                    'net_value' => $fee['net_value'],
                    'rack_value' => $fee['rack_value'],
                    'tax' => $fee['tax'],
                    'type' => $fee['type'],
                ]
            );
        }
    }

    private function updateOrDescriptiveContent(ExpediaContent $expediaContent, Hotel $hotel): void
    {
        $descriptions = $expediaContent->expediaSlave->descriptions;

        foreach ($descriptions as $sectionName => $description) {
            $section = HotelDescriptiveContentSection::updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'section_name' => $sectionName,
                ],
                [
                    'start_date' => null, // Assuming no start_date provided
                    'end_date' => null, // Assuming no end_date provided
                ]
            );

            $sectionId = $section->id;

            HotelDescriptiveContent::updateOrCreate(
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
        $sectionId = HotelImageSection::where('name', 'room')->value('id');

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
                    $hotelImage = HotelImage::firstOrNew(['image_url' => $link['href']]);
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

    private function updateOrCreateKey(ExpediaContent $expediaContent, Hotel $hotel): void
    {
        $key_id = Mapping::where('supplier_id', $expediaContent->property_id)
            ->where('supplier', SupplierNameEnum::EXPEDIA->value)
            ->value('giata_id');

        if (!$key_id) return;

        $keyMapping = [
            'hotel_id' => $hotel->id,
            'key_id' => $key_id,
            'key_mapping_owner_id' => KeyMappingOwner::where('name', 'GIATA')->value('id'),
        ];
        KeyMapping::updateOrCreate(
            ['hotel_id' => $hotel->id],
            $keyMapping
        );
    }

    private function updateOrCreateHotelAttributes(ExpediaContent $expediaContent, Hotel $hotel): void
    {
        $flattenedAttributes = [];
        foreach ($expediaContent->expediaSlave->attributes as $category => $items) {
            foreach ($items as $item) {
                $flattenedAttributes[] = [
                    'hotel_id' => $hotel->id,
                    'name' => $category,
                    'attribute_value' => $item['name'],
                ];
            }
        }
        foreach ($flattenedAttributes as $attribute) {
            HotelAttribute::updateOrCreate(
                ['hotel_id' => $attribute['hotel_id'], 'attribute_value' => $attribute['name']],
                $attribute
            );
        }
    }

    private function updateOrCreateHotelImages(ExpediaContent $expediaContent, Hotel $hotel): void
    {
        $imageGallery = ImageGallery::firstOrCreate(
            ['gallery_name' => 'Expedia Gallery Hotel ' . $hotel->name],
            ['description' => 'Gallery for Expedia images for hotel ' . $hotel->name]
        );

        $sectionId = HotelImageSection::where('name', 'hotel')->value('id');

        $upsertedImages = [];
        foreach ($expediaContent->expediaSlave->images as $image) {
            $tag = Arr::get($image, 'caption', 'No caption');
            foreach ($image['links'] as $weight => $data) {
                $hotelImage = HotelImage::firstOrNew(['image_url' => $data['href'], 'section_id' => $sectionId]);
                $hotelImage->tag = $tag;
                $hotelImage->weight = $weight;
                $hotelImage->section_id = $sectionId;
                $hotelImage->save();
                $upsertedImages[] = $hotelImage->id;
            }
        }

        $imageGallery->images()->syncWithoutDetaching($upsertedImages);
        // Step 5: Attach the ImageGallery to the Hotel
        $hotel->galleries()->syncWithoutDetaching([$imageGallery->id]);
    }
}
