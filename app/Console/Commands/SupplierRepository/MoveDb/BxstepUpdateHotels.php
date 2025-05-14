<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Models\Hotel;

class BxstepUpdateHotels extends Command
{
    protected $signature = 'move-db:hotels-update {file?}';

    protected $description = 'Update hotels';

    public function handle()
    {
        $this->warn('-> Bx step Update Hotels Data');

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

        $records = iterator_to_array($csv);
        $totalRecords = count($records);

        $this->newLine();

        $this->withProgressBar($records, function ($record) use ($addHotelAction) {

            $existingHotel = Hotel::where('giata_code', $record['code'])->first();
            if (! $existingHotel) {
                $this->output->write("\033[1A\r\033[KHotel with GIATA code {$record['code']} not found.\n");

                return;
            }

            $vendor_id = $existingHotel->product->vendor_id;

            $data = [
                'giata_code' => $record['code'],
                'product' => [
                    'vendor_id' => $vendor_id,
                ],
            ];

            $startTime = microtime(true);

            try {
                $addHotelAction->saveWithGiataCode($data);
                $executionTime = microtime(true) - $startTime;
                $this->output->write("\033[1A\r\033[KGIATA code {$record['code']} updated in {$executionTime} seconds.\n");
            } catch (\Exception $e) {
                $this->output->write("\033[1A\r\033[KFailed to update hotel with GIATA code {$record['code']}: {$e->getMessage()}\n");
            }
        });

        $this->info("\nHotels Update hotels - successfully.");
    }
}
