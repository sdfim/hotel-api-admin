<?php

namespace Modules\HotelContentRepository\DB\Seeders;

use Illuminate\Database\Seeder;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class HotelContentRepositorySeeder extends Seeder
{
    public function run()
    {
        // Seed ContentSource
        $contentSources = ContentSourceEnum::cases();
        foreach ($contentSources as $source) {
            ContentSource::firstOrCreate(['name' => $source]);
        }

        // Seed HotelImageSection
        $hotelImageSections = ['hotel', 'room', 'exterior', 'amenities', 'gallery'];
        foreach ($hotelImageSections as $section) {
            ImageSection::firstOrCreate(['name' => $section]);
        }

        // Seed KeyMappingOwner
        $keyMappingOwners = ['TerraMare system'];
        foreach ($keyMappingOwners as $owner) {
            KeyMappingOwner::firstOrCreate(['name' => $owner]);
        }
    }
}
