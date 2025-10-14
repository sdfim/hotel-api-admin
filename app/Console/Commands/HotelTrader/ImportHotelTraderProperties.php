<?php

namespace App\Console\Commands\HotelTrader;

use App\Models\HotelTraderProperty;
use App\Models\Supplier;
use App\Traits\ExceptionReportTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\ExceptionReportController;

class ImportHotelTraderProperties extends Command
{
    use ExceptionReportTrait;

    protected $signature = 'hoteltrader:import-properties {hotelCsvPath?} {roomCsvPath?}';

    protected $description = 'Import hotel and room data from HotelTrader CSV files';

    protected int $hotel_traeder_id;

    protected ?string $report_id;

    protected float|string $current_time;

    protected array $execution_times = [];

    public function __construct(
        protected ExceptionReportController $apiExceptionReport
    ) {
        parent::__construct();
        $this->execution_times['main'] = microtime(true);
        $this->execution_times['step'] = microtime(true);
        $this->execution_times['report'] = microtime(true);
    }

    public function handle()
    {
        $this->hotel_traeder_id = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()?->id ?? 0;
        $this->report_id = Str::uuid()->toString();

        $hotelCsv = $this->argument('hotelCsvPath') ?? 'app/Console/Commands/HotelTrader/HTR_property_static_data.csv';
        $roomCsv = $this->argument('roomCsvPath') ?? 'app/Console/Commands/HotelTrader/HTR_rooms.csv';

        $storageDisk = Storage::disk('public');
        if (! $storageDisk->exists($hotelCsv) || ! $storageDisk->exists($roomCsv)) {
            $this->error('CSV files not found.');

            return 1;
        }

        $this->info('Starting HotelTrader properties import...');

        $this->info('Reading hotel CSV file...');
        $hotels = $this->readCsvFromStorage($storageDisk, $hotelCsv);
        $this->info('Hotel CSV file loaded. Rows: '.count($hotels));

        $this->info('Reading room CSV file...');
        $rooms = $this->readCsvFromStorage($storageDisk, $roomCsv);
        $this->info('Room CSV file loaded. Rows: '.count($rooms));

        $this->info('Grouping rooms by propertyId...');
        // Group rooms by propertyId
        $roomsByProperty = [];
        foreach ($rooms as $room) {
            $propertyId = $room['propertyId'] ?? null;
            if ($propertyId) {
                $roomsByProperty[$propertyId][] = $room;
            }
        }
        $this->info('Rooms grouped by propertyId. Unique properties: '.count($roomsByProperty));

        $count = 0;
        $skipped = 0;
        $total = count($hotels);
        foreach ($hotels as $idx => $hotel) {
            $propertyId = $hotel['propertyId'] ?? null;
            if (! $propertyId) {
                $skipped++;

                continue;
            }
            $hotelRooms = $roomsByProperty[$propertyId] ?? [];
            $hotel['rooms'] = $hotelRooms;

            try {
                HotelTraderProperty::updateOrCreate(
                    ['propertyId' => $propertyId],
                    $hotel
                );
                $count++;
            } catch (\Throwable $e) {
                $errorContent = json_encode([
                    'propertyId' => $propertyId,
                    'hotel' => $hotel,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->saveErrorReport('ImportHotelTraderProperties', 'HotelTraderProperty import error', $errorContent);
                $this->error('ImportHotelTraderProperties _ HotelTraderProperty import error: '.$e->getMessage());
            }
            if (($count + $skipped) % 100 === 0) {
                $this->info('Progress: '.($count + $skipped)."/$total processed. Imported: $count, Skipped: $skipped");
            }
        }

        $this->info("Import complete. Imported $count properties. Skipped $skipped hotels without propertyId.");

        return 0;
    }

    private function readCsvFromStorage($storageDisk, $file)
    {
        $data = [];
        // Save CSV to a temp file for SplFileObject parsing
        $tmpPath = sys_get_temp_dir().'/hoteltrader_'.uniqid().'.csv';
        file_put_contents($tmpPath, $storageDisk->get($file));
        $csv = new \SplFileObject($tmpPath);
        $csv->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $header = null;
        foreach ($csv as $fields) {
            if ($fields === [null] || $fields === false) {
                continue;
            }
            if (! $header) {
                $header = $fields;

                continue;
            }
            if (count($header) !== count($fields)) {
                logger()->error('ImportHotelTraderProperties _ CSV row skipped due to column count mismatch', ['row' => $fields]);
                $this->saveErrorReport('ImportHotelTraderProperties', 'CSV row skipped due to column count mismatch', json_encode(['row' => $fields]));

                continue;
            }
            $data[] = array_combine($header, $fields);
        }
        unlink($tmpPath);

        return $data;
    }
}
