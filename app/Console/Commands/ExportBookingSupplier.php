<?php

namespace App\Console\Commands;

use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\Writer;

class ExportBookingSupplier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:booking_supplier';

    protected $description = 'Export booking items supplier field';

    protected const BATCH = 100;

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $exportFilename = 'exports/obe_reservation_hotels_booking_supplier.csv';
        $csvWriter = Writer::createFromString();

        $csvWriter->insertOne([
            'booking_number',
            'booking_supplier',
        ]);

        $suppliers = Supplier::all()->toArray();
        $supplierMap = [];
        foreach ($suppliers as $supplier) {
          $supplierMap[$supplier['id']] = $supplier['name'];
        }

        $query = ApiBookingItem::query()
            ->whereNotNull('booking_item')
            ->whereNotNull('supplier_id')
            ->select('booking_item', 'supplier_id');
        $total = $query->count();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $this->info('Starting '.Carbon::now());
        for ($offset = 0; $offset < $total; $offset += self::BATCH)
        {
            $percentage = round(($offset / $total) * 100);
            //$this->info("Offset $offset - $percentage% - ".Carbon::now());
            $apiBookingItemsBatch= $query->skip($offset)->take(self::BATCH)->get();

            $mappedApiBookingItemsBatch = $apiBookingItemsBatch->map(function ($item) use($supplierMap) {
                return [
                  'booking_item' => $item['booking_item'],
                  'supplier' => $supplierMap[$item['supplier_id']] ?? null,
                ];
            })->toArray();

            // $this->info("Finished batch number $batchNumber ".Carbon::now());
            $csvWriter->insertAll($mappedApiBookingItemsBatch);
            $bar->advance(self::BATCH);
        }
        Storage::put($exportFilename, $csvWriter->toString());
        $this->info('Finsihed '.Carbon::now());
    }
}
