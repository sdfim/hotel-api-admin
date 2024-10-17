<?php

namespace App\Console\Commands\HotelContentRepository;

use App\Models\ExpediaContent;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAffiliation;
use Modules\HotelContentRepository\Models\HotelAttribute;

class TransformExpediaContentToHotels extends Command
{

    protected $signature = 'transform:expedia-to-hotels';
    protected $description = 'Transform ExpediaContent records to Hotel related models';

    public function handle()
    {
        $expediaContents = ExpediaContent::with('expediaSlave')->cursor();

        foreach ($expediaContents as $expediaContent) {
            $this->transformAndSave($expediaContent);
        }

        $this->info('Transformation completed successfully.');
    }

    private function transformAndSave($expediaContent)
    {
        $expediaId = ContentSource::where('name', SupplierNameEnum::EXPEDIA->value)->first()->id;
        // Example transformation logic

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
        $this->updateOrCreateHotelAttributes($expediaContent, $hotel);

        dd($hotel);

        // HotelDescriptiveContent::create([...]);
        // HotelDescriptiveContentSection::create([...]);
        // HotelFeeTax::create([...]);

        // ImageGallery::create([...]);
        // HotelImage::create([...]);
        // HotelImageSection::create([...]);

        // HotelInformativeService::create([...]);
        // HotelPromotion::create([...]);
        // HotelRoom::create([...]);

        // KeyMapping::create([...]);
        // TravelAgencyCommission::create([...]);

        // Example for expediaSlave related data
        if ($expediaContent->expediaSlave) {
            // Process expediaSlave data
        }
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
}
