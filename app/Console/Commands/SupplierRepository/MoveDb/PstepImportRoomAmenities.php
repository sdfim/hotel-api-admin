<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\HotelRoom;

class PstepImportRoomAmenities extends Command
{
    protected $signature = 'move-db:room-amenities';

    protected $description = 'Import room amenities from donor database';

    public function handle()
    {
        $this->warn('-> P step Import Room Amenities');

        $donorAmenities = DB::connection('donor')->table('hotel_room_villa_detail_amenities')
            ->leftJoin('hotel_room_villa_details', 'hotel_room_villa_detail_amenities.hotel_room_villa_detail_id', '=', 'hotel_room_villa_details.id')
            ->leftJoin('hotel_rooms', 'hotel_room_villa_details.hotel_room_id', '=', 'hotel_rooms.id')
            ->leftJoin('hotel_room_villa_amenities', 'hotel_room_villa_detail_amenities.hotel_room_villa_amenity_id', '=', 'hotel_room_villa_amenities.id')
            ->select('hotel_rooms.id as room_id', 'hotel_room_villa_amenities.name as amenity_name', 'hotel_room_villa_details.max_amount_of_people as max_occupancy')
            ->get();

        $this->newLine();

        $this->withProgressBar($donorAmenities, function ($donorAmenity) {
            $hotelRoom = HotelRoom::whereHas('crm', function ($query) use ($donorAmenity) {
                $query->where('crm_room_id', $donorAmenity->room_id);
            })->first();

            if (! $hotelRoom) {
                return;
            }

            $hotelRoom->max_occupancy = $donorAmenity->max_occupancy;
            $hotelRoom->save();

            $attribute = ConfigAttribute::firstOrCreate(
                ['name' => $donorAmenity->amenity_name],
                ['default_value' => $donorAmenity->amenity_name]
            );

            $hotelRoom->attributes()->syncWithoutDetaching([$attribute->id]);

            $this->output->write("\033[1A\r\033[KImporting room amenities for hotel room {$hotelRoom->name} ({$hotelRoom->id})\n");
        });

        $this->info("\nRoom amenities imported successfully.");
    }
}
