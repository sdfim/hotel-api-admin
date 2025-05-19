<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelCrmMapping;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\ImageSection;

class FstepImportHotelImages extends Command
{
    protected $signature = 'move-db:hotel-images';

    protected $description = 'Import hotel images from the database';

    public function handle()
    {
        $this->warn('-> F step Import Hotel Images');

        $mappings = HotelCrmMapping::all();
        $section = ImageSection::where('name', 'hotel')->first();

        $this->newLine();

        $this->withProgressBar($mappings, function ($mapping) use ($section) {
            $crmHotelId = $mapping->crm_hotel_id;
            $giataCode = $mapping->giata_code;

            $hotelPictures = DB::connection('donor')->select('
                select * from hotel_pictures
                where hotel_id = ?
            ', [$crmHotelId]);

            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($crmHotelId) {
                $query->where('crm_hotel_id', $crmHotelId);
            })->first();

            if ($hotel) {
                $galleryName = "Hotel - {$hotel->product->name} - {$giataCode}";
                $galleryDescription = "Product Image Gallery: Hotel - {$hotel->product->name} - {$giataCode}";

                $gallery = ImageGallery::firstOrCreate(
                    ['gallery_name' => $galleryName],
                    ['description' => $galleryDescription]
                );

                if (! $gallery->products()->where('product_id', $hotel->id)->exists()) {
                    $gallery->products()->attach($hotel->id);
                }

                foreach ($hotelPictures as $picture) {
                    $image = Image::firstOrCreate(
                        [
                            'section_id' => $section->id,
                            'tag' => $picture->type,
                            'weight' => $picture->position,
                            'image_url' => $picture->path,
                        ],
                        [
                            'section_id' => $section->id,
                            'tag' => $picture->type,
                            'weight' => $picture->position,
                            'image_url' => 'Hotels/Pictures/'.$picture->path,
                            'alt' => $picture->type.' image',
                            'source' => 'crm',
                        ]
                    );

                    if (! $gallery->images()->where('image_id', $image->id)->exists()) {
                        $gallery->images()->attach($image->id);
                    }
                }

                $this->output->write("\033[1A\r\033[KGallery created for hotel: {$hotel->product->name}\n");
            } else {
                $this->output->write("\033[1A\r\033[KHotel not found for crm_hotel_id: {$crmHotelId}\n");
            }
        });

        $this->info("\nHotel images imported successfully.");
    }
}
