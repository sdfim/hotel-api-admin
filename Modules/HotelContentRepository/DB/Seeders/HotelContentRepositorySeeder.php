<?php

namespace Modules\HotelContentRepository\DB\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class HotelContentRepositorySeeder extends Seeder
{
    public function run()
    {
        // Seed ContentSource
        $contentSources = ['Expedia', 'IcePortal', 'Internal'];
        foreach ($contentSources as $source) {
            ContentSource::firstOrCreate(['name' => $source]);
        }

        // Seed HotelImageSection
        $hotelImageSections = ['hotel', 'room', 'exterior', 'amenities', 'gallery'];
        foreach ($hotelImageSections as $section) {
            ImageSection::firstOrCreate(['name' => $section]);
        }

        // Seed KeyMappingOwner
        $keyMappingOwners = ['GIATA', 'UJV system'];
        foreach ($keyMappingOwners as $owner) {
            KeyMappingOwner::firstOrCreate(['name' => $owner]);
        }

        // Seed HotelAgeRestrictionType
        $restrictionTypes = [
            ['name' => 'Max Child Age', 'description' => 'The maximum age for a child to be considered in this category.'],
            ['name' => 'Max Infant Age', 'description' => 'The maximum age for an infant to be considered in this category.'],
            ['name' => 'Adults Only', 'description' => 'Indicates that the hotel is for adults only.'],
            ['name' => 'Adults Only Sections', 'description' => 'Indicates that certain sections of the hotel are for adults only.'],
            ['name' => 'Minimum Age', 'description' => 'The minimum age required to stay at the hotel.'],

        ];
        foreach ($restrictionTypes as $type) {
            HotelAgeRestrictionType::firstOrCreate($type);
        }
    }
}
