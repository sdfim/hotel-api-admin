<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\RoomCrm;

class EstepImportRooms extends Command
{
    protected $signature = 'move-db:rooms';

    protected $description = 'Import rooms from the database';

    public function handle()
    {
        $this->warn('-> E step Import Rooms');

        $rooms = DB::connection('donor')->select('
            select distinct r.name as name, r.id as id, r.hotel_id as hotel_id, r.description as description
            from hotel_rooms as r
            where r.deleted_at is null
        ');

        $this->newLine();

        $this->withProgressBar($rooms, function ($room) {
            $room = (array) $room;
            $name = Arr::get($room, 'name');
            $description = Arr::get($room, 'description');
            $hotel_id = Arr::get($room, 'hotel_id');

            $hotelSystem = Hotel::with('crmMapping')
                ->whereHas('crmMapping', function ($query) use ($hotel_id) {
                    $query->where('crm_hotel_id', $hotel_id);
                })->first();

            $hotel_id = $hotelSystem?->id;

            if ($hotel_id) {
                $existingRoomCrm = RoomCrm::where('crm_room_id', $room['id'])->first();

                if (! $existingRoomCrm) {
                    try {
                        $hotelRoom = new HotelRoom([
                            'hotel_id' => $hotel_id,
                            'name' => $name,
                            'description' => $description,
                        ]);

                        $hotelRoom->save();

                        $roomCrm = new RoomCrm([
                            'room_id' => $hotelRoom->id,
                            'crm_room_id' => $room['id'],
                        ]);

                        $roomCrm->save();

                        $this->output->write("\033[1A\r\033[KProcessing room: {$name}\n");
                    } catch (\Exception $e) {
                        $this->output->write("\033[1A\r\033[KError processing room: {$name}\n");
                        \Log::error('Error processing room: '.$e->getMessage());
                        \Log::error('Error processing room: '.$e->getTraceAsString());
                    }
                } else {
                    $this->output->write("\033[1A\r\033[KRoom already exists: {$name}\n");
                }
            }
        });

        $this->info("\nRooms imported successfully.");
    }
}
