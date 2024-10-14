<?php

namespace Modules\HotelContentRepository\DB\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\HotelImageSection;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class HotelContentRepositorySeeder extends Seeder
{
    public function run()
    {
        // Seed ContentSource
        $contentSources = ['Expedia', 'IcePortal', 'Internal'];
        foreach ($contentSources as $source) {
            ContentSource::create(['name' => $source]);
        }

        // Seed HotelImageSection
        $hotelImageSections = ['hotel', 'room', 'exterior', 'amenities', 'gallery'];
        foreach ($hotelImageSections as $section) {
            HotelImageSection::create(['name' => $section]);
        }

        // Seed KeyMappingOwner
        $keyMappingOwners = ['GIATA', 'UJV system'];
        foreach ($keyMappingOwners as $owner) {
            KeyMappingOwner::create(['name' => $owner]);
        }
    }
}
