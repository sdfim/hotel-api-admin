<?php

namespace App\Console\Commands\IcePortal;

use App\Models\Mapping;
use App\Models\Supplier;
use App\Traits\ExceptionReportTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\API\Suppliers\IcePortal\Client\IceHBSIClient;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\ExceptionReportController;

class DownloadIcePortalData extends Command
{
    use ExceptionReportTrait;

    protected $signature = 'download-iceportal-data';

    protected $description = 'Download IcePortal data';

    protected array $current_time = [];

    protected int $iceportal_id;

    protected ?string $report_id;

    public function __construct(
        protected IceHBSIClient $client,
        protected ExceptionReportController $apiExceptionReport
    ) {
        parent::__construct();
        $this->current_time['main'] = microtime(true);
        $this->current_time['step'] = microtime(true);
        $this->current_time['report'] = microtime(true);
    }

    public function handle(): void
    {
        $this->iceportal_id = Supplier::where('name', SupplierNameEnum::ICE_PORTAL->value)->first()?->id ?? 0;
        $this->report_id = Str::uuid()->toString();

        // Статистика для итоговых отчетов
        $successfulItems = 0;
        $failedItems = 0;
        $processedSupplierIds = [];

        $this->saveSuccessReport('DownloadIcePortalData', 'Start downloading data', json_encode([
            'execution_time' => $this->executionTime('report').' sec',
        ]));

        $mapperItems = Cache::remember('iceportal_mapper_items', 0.5, function () {
            return Mapping::IcePortal()
                ->where('supplier', 'IcePortal')
                ->get();
        });

        $this->saveSuccessReport('DownloadIcePortalData', 'Mapper items loaded', json_encode([
            'items_count' => $mapperItems->count(),
            'execution_time' => $this->executionTime('report').' sec',
        ]));

        $batchNumber = 1;
        $mapperItems->chunk(10)->each(function ($chunk) use (&$batchNumber, &$successfulItems, &$failedItems, &$processedSupplierIds) {
            $st = microtime(true);

            $supplierIds = $chunk->pluck('supplier_id')->toArray();
            $supplierIdsString = implode(', ', $supplierIds);

            // Добавляем ID поставщиков в общий список
            $processedSupplierIds = array_merge($processedSupplierIds, $supplierIds);

            try {
                $this->client->processListings($chunk, []);

                // Успешно обработанные элементы
                $successfulItems += $chunk->count();

                $this->info('Batch '.$batchNumber.': '.$supplierIdsString);
                $this->info('Total Time: '.(microtime(true) - $st));
                $this->info('---------------------------------');
            } catch (\Exception $e) {
                // Неудачно обработанные элементы
                $failedItems += $chunk->count();

                $this->error('Error processing batch '.$batchNumber.': '.$e->getMessage());

                // Записываем ошибки, так как это критическая информация
                $this->saveErrorReport('DownloadIcePortalData', 'Error processing batch', json_encode([
                    'batch' => $batchNumber,
                    'supplier_ids' => $supplierIdsString,
                    'items_count' => $chunk->count(),
                    'getMessage' => $e->getMessage(),
                    'getTraceAsString' => $e->getTraceAsString(),
                    'execution_time' => (microtime(true) - $st).' sec',
                ]));
            }

            $batchNumber++;
        });

        $totalTime = microtime(true) - $this->current_time['main'];

        // Сохраняем итоговый отчет с полной статистикой
        $this->saveSuccessReport('DownloadIcePortalData', 'All data processed successfully', json_encode([
            'total_batches' => $batchNumber - 1,
            'total_items' => $mapperItems->count(),
            'successful_items' => $successfulItems,
            'failed_items' => $failedItems,
            'unique_supplier_ids_processed' => count(array_unique($processedSupplierIds)),
            'total_execution_time' => $totalTime.' sec',
            'memory_peak_usage' => (memory_get_peak_usage() / 1024 / 1024).' MB',
        ]));
    }

    /**
     * Calculate execution time for provided timer key
     */
    private function executionTime(string $key): float
    {
        $execution_time = (microtime(true) - $this->current_time[$key]);
        $this->current_time[$key] = microtime(true);

        return $execution_time;
    }
}
