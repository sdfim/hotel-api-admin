<?php

namespace App\Console\Commands\HotelTrader;

use App\Models\HotelTraderProperty;
use Illuminate\Console\Command;

class ImportHotelTraderProperties extends Command
{
    protected $signature = 'hoteltrader:import-properties';

    protected $description = 'Import hotel and room data from HotelTrader CSV files';

    public function handle()
    {
        $hotelCsv = base_path('app/Console/Commands/HotelTrader/HTR_property_static_data.csv');
        $roomCsv = base_path('app/Console/Commands/HotelTrader/HTR_rooms.csv');

        if (! file_exists($hotelCsv) || ! file_exists($roomCsv)) {
            $this->error('CSV files not found.');

            return 1;
        }

        $hotels = $this->readCsv($hotelCsv);
        $rooms = $this->readCsv($roomCsv);

        // Group rooms by propertyId
        $roomsByProperty = [];
        foreach ($rooms as $room) {
            $propertyId = $room['propertyId'] ?? null;
            if ($propertyId) {
                $roomsByProperty[$propertyId][] = $room;
            }
        }

        $count = 0;
        foreach ($hotels as $hotel) {
            $propertyId = $hotel['propertyId'] ?? null;
            if (! $propertyId) {
                continue;
            }

            $hotelRooms = $roomsByProperty[$propertyId] ?? [];
            $hotel['rooms'] = $hotelRooms;

            HotelTraderProperty::updateOrCreate(
                ['propertyId' => $propertyId],
                $hotel
            );
            $count++;
        }

        $this->info("Imported $count properties.");

        return 0;
    }

    private function readCsv($file)
    {
        $data = [];
        if (($handle = fopen($file, 'r')) !== false) {
            $header = null;
            while (($row = fgetcsv($handle)) !== false) {
                if (! $header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}
