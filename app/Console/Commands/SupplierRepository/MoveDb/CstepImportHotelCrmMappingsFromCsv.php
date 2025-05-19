<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Modules\HotelContentRepository\Models\HotelCrmMapping;

class CstepImportHotelCrmMappingsFromCsv extends Command
{
    protected $signature = 'move-db:hotel-crm-mappings-from-csv-to-db {file?}';

    protected $description = 'Import hotel CRM mappings from a CSV file and update or insert records in the HotelCrmMapping table';

    public function handle()
    {
        $this->warn('-> C step Import Hotel CRM Mappings From csv to db');

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

        $records = iterator_to_array($csv);
        $totalRecords = count($records);

        $this->newLine();

        $this->withProgressBar($records, function ($record) {
            $data = [
                'giata_code' => $record['code'],
                'crm_hotel_id' => $record['id'],
            ];

            try {
                HotelCrmMapping::updateOrCreate(
                    ['giata_code' => $data['giata_code']],
                    ['crm_hotel_id' => $data['crm_hotel_id']]
                );
                $this->output->write("\033[1A\r\033[KGIATA code {$data['giata_code']} updated or inserted.\n");
            } catch (\Exception $e) {
                $this->output->write("\033[1A\r\033[KFailed to update or insert GIATA code {$data['giata_code']}: {$e->getMessage()}\n");
            }
        });

        $this->info("\nHotel CRM mappings imported - successfully.");
    }
}
