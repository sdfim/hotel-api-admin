<?php

namespace App\Console\Commands\Hbsi;

use App\Models\Mapping;
use App\Models\Supplier;
use App\Traits\ExceptionReportTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\ExceptionReportController;

class FetchHbsiContentBulk extends Command
{
    use ExceptionReportTrait;

    protected $signature = 'hbsi:fetch-content-bulk';

    protected $description = 'Fetch descriptive content for all HBSI hotel codes using FetchHbsiContent';

    protected array $current_time = [];

    protected ?string $report_id;

    protected $apiExceptionReport;

    protected int $hbsi_supplier_id;

    public function __construct(ExceptionReportController $apiExceptionReport)
    {
        parent::__construct();
        $this->apiExceptionReport = $apiExceptionReport;
        $this->report_id = Str::uuid()->toString();
        $this->current_time['main'] = microtime(true);
        $this->current_time['step'] = microtime(true);
    }

    private function resolveHbsiSupplierId(): int
    {
        return Supplier::where('name', SupplierNameEnum::HBSI->value)->first()?->id ?? 0;
    }

    private function executionTime(string $key): float
    {
        $execution_time = (microtime(true) - $this->current_time[$key]);
        $this->current_time[$key] = microtime(true);

        return $execution_time;
    }

    private function getSupplierIdForReport(): int
    {
        return $this->resolveHbsiSupplierId();
    }

    public function handle()
    {
        $hotelCodes = Mapping::HBSI()->pluck('supplier_id')->toArray();
        $total = count($hotelCodes);
        $successfulItems = 0;
        $failedItems = 0;
        $processedHotelCodes = [];

        $this->saveSuccessReport('FetchHbsiContentBulk', 'Start bulk fetch', json_encode([
            'total_codes' => $total,
            'execution_time' => $this->executionTime('step').' sec',
        ]));

        if ($total === 0) {
            $this->warn('No HBSI hotel codes found.');
            $this->saveErrorReport('FetchHbsiContentBulk', 'No HBSI hotel codes found', json_encode([
                'execution_time' => $this->executionTime('main').' sec',
            ]));

            return;
        }
        $this->info("Found $total HBSI hotel codes. Starting fetch...");
        foreach ($hotelCodes as $idx => $hotelCode) {
            if (Cache::has('hbsi_processed_'.$hotelCode)) {
                continue;
            }

            $st = microtime(true);
            $this->line("[$idx/$total] Fetching content for hotelCode: $hotelCode");

            $maxAttempts = 3;
            $attempt = 0;
            $success = false;
            while ($attempt < $maxAttempts && ! $success) {
                try {
                    $exitCode = Artisan::call('hbsi:fetch-content', [
                        'hotelCode' => $hotelCode,
                    ]);
                    $output = Artisan::output();
                    $this->line($output);
                    $success = true;
                } catch (\Throwable $e) {
                    $attempt++;
                    if ($attempt < $maxAttempts) {
                        $this->warn("Error for hotelCode $hotelCode: {$e->getMessage()}. Retrying in 10 seconds (attempt $attempt/$maxAttempts)...");
                        sleep(10);
                    } else {
                        $this->error("All attempts failed for hotelCode $hotelCode: {$e->getMessage()}");
                        $output = $e->getMessage();
                        $exitCode = 1;
                    }
                }
            }

            $processedHotelCodes[] = $hotelCode;
            if ($exitCode === 0) {
                $successfulItems++;
                $this->saveSuccessReport('FetchHbsiContentBulk', 'Fetched content for hotelCode', json_encode([
                    'hotelCode' => $hotelCode,
                    'output' => $output,
                    'execution_time' => (microtime(true) - $st).' sec',
                ]));
            } else {
                $failedItems++;
                $this->saveErrorReport('FetchHbsiContentBulk', 'Failed to fetch content for hotelCode', json_encode([
                    'hotelCode' => $hotelCode,
                    'output' => $output,
                    'execution_time' => (microtime(true) - $st).' sec',
                ]));
            }

            Cache::put('hbsi_processed_'.$hotelCode, true, now()->addHours(2));
        }
        $totalTime = microtime(true) - $this->current_time['main'];
        $this->info('Bulk fetch complete.');
        $this->saveSuccessReport('FetchHbsiContentBulk', 'Bulk fetch complete', json_encode([
            'total_codes' => $total,
            'successful_items' => $successfulItems,
            'failed_items' => $failedItems,
            'unique_hotel_codes_processed' => count(array_unique($processedHotelCodes)),
            'total_execution_time' => $totalTime.' sec',
            'memory_peak_usage' => (memory_get_peak_usage() / 1024 / 1024).' MB',
        ]));
    }
}
