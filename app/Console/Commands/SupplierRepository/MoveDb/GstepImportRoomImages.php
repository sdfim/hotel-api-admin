<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\Models\RoomCrm;

class GstepImportRoomImages extends Command
{
    protected $signature = 'move-db:room-images';

    protected $description = 'Import hotel images from the database';

    public function handle()
    {
        $this->warn('-> G step Import Room Images');

        $mappings = RoomCrm::all();
        $section = ImageSection::where('name', 'room')->first();

        $this->newLine();

        $this->withProgressBar($mappings, function ($mapping) use ($section) {
            $crmRoomId = $mapping->crm_room_id;
            $roomId = $mapping->room_id;

            $roomPictures = DB::connection('donor')->select('
                select * from hotel_room_pictures
                where hotel_room_id = ?
            ', [$crmRoomId]);

            $room = HotelRoom::whereHas('crm', function ($query) use ($crmRoomId) {
                $query->where('crm_room_id', $crmRoomId);
            })->first();

            if ($room and count($roomPictures) > 0) {
                $galleryName = "Hotel - {$room->hotel->product->name} - {$room->hotel->giata_code} - Room {$room->id}";
                $galleryDescription = "Room Image Gallery: Hotel - {$room->hotel->product->name} - {$room->hotel->giata_code} - Room {$room->id}";

                $gallery = ImageGallery::firstOrCreate(
                    ['gallery_name' => $galleryName],
                    ['description' => $galleryDescription]
                );

                if (! $gallery->hotelRooms()->where('id', $room->id)->exists()) {
                    $gallery->hotelRooms()->attach($room->id);
                }

                foreach ($roomPictures as $picture) {
                    $image = Image::firstOrCreate(
                        [
                            'section_id' => $section->id,
                            'tag' => 'Room',
                            'weight' => $picture->position,
                            'image_url' => $picture->path,
                        ],
                        [
                            'section_id' => $section->id,
                            'tag' => 'Room',
                            'weight' => $picture->position,
                            'image_url' => 'Rooms/Pictures/'.$picture->path,
                            'alt' => 'Room image',
                            'source' => 'crm',
                        ]
                    );

                    if (! $gallery->images()->where('image_id', $image->id)->exists()) {
                        $gallery->images()->attach($image->id);
                    }
                }

                $this->output->write("\033[1A\r\033[KGallery created for room: {$room->id}\n");
            } else {
                $this->output->write("\033[1A\r\033[KNo room pictures found for crm_hotel_id: {$crmRoomId}\n");
            }
        });

        $this->info("\nRoom images imported successfully.");
    }
}
