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

        $this->info('Starting download of HotelTrader files...');
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Start downloading files', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $this->info('Downloading property mapping file...');
        $propertySaved = $this->downloadFileStream($this->propertyUrl, $storageDisk->path($propertyFile));
        if ($propertySaved === false) {
            $this->error('Failed to download property mapping file.');
            $this->saveErrorReport('DownloadHotelTraderFilesAndImport', 'Failed to download property mapping file', json_encode([
                'execution_time' => $this->executionTime('step').' sec',
            ]));

            return 1;
        }
        $this->info('Property mapping file downloaded successfully.');
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Property mapping file downloaded', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $this->info('Downloading rooms mapping file...');
        $roomsSaved = $this->downloadFileStream($this->roomsUrl, $storageDisk->path($roomsFile));
        if ($roomsSaved === false) {
            $this->error('Failed to download rooms mapping file.');
            $this->saveErrorReport('DownloadHotelTraderFilesAndImport', 'Failed to download rooms mapping file', json_encode([
                'execution_time' => $this->executionTime('step').' sec',
            ]));

            return 1;
        }
        $this->info('Rooms mapping file downloaded successfully.');
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Rooms mapping file downloaded', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $this->info('Starting import of downloaded files...');
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Files downloaded. Starting import...', json_encode([
            'execution_time' => $this->executionTime('step').' sec',
        ]));
        $exitCode = Artisan::call('hoteltrader:import-properties', [
            'hotelCsvPath' => $propertyFile,
            'roomCsvPath' => $roomsFile,
        ], $this->output);
        $this->info('Import finished.');
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'Import finished', json_encode([
            'artisan_output' => Artisan::output(),
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        $totalTime = microtime(true) - $this->current_time['main'];
        $this->info('All steps completed.');
        $this->saveSuccessReport('DownloadHotelTraderFilesAndImport', 'All steps completed', json_encode([
            'total_execution_time' => $totalTime.' sec',
            'memory_peak_usage' => (memory_get_peak_usage() / 1024 / 1024).' MB',
        ]));

        return $exitCode;
    }

    /**
     * Download a file from a URL and save it directly to disk in chunks.
     * Returns true on success, false on failure.
     */
    private function downloadFileStream($url, $localPath)
    {
        try {
            $remoteStream = fopen($url, 'r');
            if ($remoteStream === false) {
                return false;
            }
            $localStream = fopen($localPath, 'w');
            if ($localStream === false) {
                fclose($remoteStream);

                return false;
            }
            while (! feof($remoteStream)) {
                $buffer = fread($remoteStream, 8192);
                if ($buffer === false) {
                    fclose($remoteStream);
                    fclose($localStream);

                    return false;
                }
                fwrite($localStream, $buffer);
            }
            fclose($remoteStream);
            fclose($localStream);

            return true;
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
