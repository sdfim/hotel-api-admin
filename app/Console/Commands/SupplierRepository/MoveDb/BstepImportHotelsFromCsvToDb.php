<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class BstepImportHotelsFromCsvToDb extends Command
{
    protected $signature = 'move-db:hotels-from-csv-to-db {file?}';

    protected $description = 'Import hotels from a CSV file and save them using AddHotel->saveWithGiataCode';

    public function handle()
    {
        $this->warn('-> B step Import Hotels From csv to db');

        $filePath = $this->argument('file') ?? 'hotels_output.csv';

        if (Storage::exists($filePath)) {
            $csv = Reader::createFromPath(Storage::path($filePath), 'r');
        } else {
            $this->info('File from Storage not found. Trying default path...');

            $filePath = base_path('app/Console/Commands/SupplierRepository/MoveDb/files/hotels_output.csv');

            if (file_exists($filePath)) {
                $csv = Reader::createFromPath($filePath, 'r');
            } else {
                $this->error('File not found in default path either.');

                return;
            }
        }

        $csv->setHeaderOffset(0);

        /* @var AddHotel $addHotelAction */
        $addHotelAction = app(AddHotel::class);

        $vendor = Vendor::updateOrCreate(
            ['name' => 'TEST'],
            [
                'address' => 'TEST',
                'lat' => 0,
                'lng' => 0,
                'verified' => 1,
                'type' => ['hotel'],
                'independent_flag' => 1,
            ]
        );

        $records = iterator_to_array($csv);
        $totalRecords = count($records);

        $this->newLine();

        $this->withProgressBar($records, function ($record) use ($addHotelAction, $vendor) {
            $data = [
                'giata_code' => $record['code'],
                'product' => [
                    'vendor_id' => $vendor->id,
                ],
            ];

            $startTime = microtime(true);

            try {
                $existingHotel = Hotel::where('giata_code', $record['code'])->first();
                if ($existingHotel) {
                    $this->output->write("\033[1A\r\033[KHotel with GIATA code {$record['code']} already exists.\n");
                } else {
                    $addHotelAction->saveWithGiataCode($data);
                    $executionTime = microtime(true) - $startTime;
                    $this->output->write("\033[1A\r\033[KGIATA code {$record['code']} imported in {$executionTime} seconds.\n");
                }
            } catch (\Exception $e) {
                $this->output->write("\033[1A\r\033[KFailed to import hotel with GIATA code {$record['code']}: {$e->getMessage()}\n");
            }
        });

        $this->info("\nHotels Import hotels from a CSV file and save them using AddHotel->saveWithGiataCode - successfully.");
    }
}
