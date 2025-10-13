<?php

namespace App\Console\Commands\HotelTrader;

use App\Models\Supplier;
use App\Traits\ExceptionReportTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\ExceptionReportController;

class DownloadHotelTraderFilesAndImport extends Command
{
    use ExceptionReportTrait;

    protected $signature = 'hoteltrader:download-and-import';

    protected $description = 'Download HotelTrader property and room files, then import them.';

    protected int $hotel_traeder_id;

    protected ?string $report_id;

    protected array $current_time = [];

    private $propertyUrl = 'https://metadata-files.hoteltrader.com/415-fora/HTR_property_static_data.csv';

    private $roomsUrl = 'https://metadata-files.hoteltrader.com/415-fora/HTR_rooms.csv';

    public function __construct(
        protected ExceptionReportController $apiExceptionReport
    ) {
        parent::__construct();
        $this->current_time['main'] = microtime(true);
        $this->current_time['step'] = microtime(true);
        $this->current_time['report'] = microtime(true);
    }

    public function handle()
    {
        $this->hotel_traeder_id = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()?->id ?? 0;
        $this->report_id = Str::uuid()->toString();

        $storageDisk = Storage::disk('public');
        $propertyFile = 'hoteltrader/HTR_property_static_data.csv';
        $roomsFile = 'hoteltrader/HTR_rooms.csv';

        // Ensure directory exists (Storage handles this on put)

        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Start downloading files', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $propertyContent = $this->downloadFile($this->propertyUrl);
        if ($propertyContent === false) {
            $this->saveErrorReport('DownloadHotelTraderFilesAndImport', 'Failed to download property mapping file', json_encode([
                'execution_time' => $this->executionTime('step').' sec',
            ]));

            return 1;
        }
        $storageDisk->put($propertyFile, $propertyContent);
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Property mapping file downloaded', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $roomsContent = $this->downloadFile($this->roomsUrl);
        if ($roomsContent === false) {
            $this->saveErrorReport('DownloadHotelTraderFilesAndImport', 'Failed to download rooms mapping file', json_encode([
                'execution_time' => $this->executionTime('step').' sec',
            ]));

            return 1;
        }
        $storageDisk->put($roomsFile, $roomsContent);
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Rooms mapping file downloaded', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Files downloaded. Starting import...', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));
        $exitCode = Artisan::call('hoteltrader:import-properties', [
            'hotelCsvPath' => $propertyFile,
            'roomCsvPath' => $roomsFile,
        ]);
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Import finished', json_encode([
            'artisan_output' => Artisan::output(),
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $totalTime = microtime(true) - $this->current_time['main'];
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'All steps completed', json_encode([
            'total_execution_time' => $totalTime.' sec',
            'memory_peak_usage' => (memory_get_peak_usage() / 1024 / 1024).' MB',
        ]));

        return $exitCode;
    }

    private function downloadFile($url)
    {
        try {
            $content = file_get_contents($url);
            if ($content === false) {
                return false;
            }

            return $content;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function executionTime(string $key): float
    {
        $execution_time = (microtime(true) - $this->current_time[$key]);
        $this->current_time[$key] = microtime(true);

        return $execution_time;
    }
}
