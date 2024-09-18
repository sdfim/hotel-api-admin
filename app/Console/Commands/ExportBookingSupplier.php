<?php

namespace App\Console\Commands;

use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Exception;
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

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        // Export vars
        $exportFilename = 'exports/obe_reservation_hotels_booking_supplier.csv';
        $csvWriter = Writer::createFromString();

        $csvWriter->insertOne([
            'booking_number',
            'booking_supplier',
        ]);

        try {
            $bookings = ApiBookingItem::whereNotNull('booking_item')
                ->whereNotNull('supplier_id')
                ->select('booking_item', 'supplier_id')
                ->get()
                ->toArray();

            $suppliers = Supplier::all()->toArray();
            $supplierMap = [];
            $this->info('booking_id = '.json_encode($suppliers));
            foreach ($suppliers as $supplier) {
              $supplierMap[$supplier['id']] = $supplier['name'];
            }

            $this->info('booking_id = '.json_encode($supplierMap));

            $mappedBooking = array_map(function ($item) use ($supplierMap) {
              return [
                  'booking_item' => $item['booking_item'],
                  'supplier' => $supplierMap[$item['supplier_id']] ?? null,
              ];
            }, $bookings);

            $csvWriter->insertAll($mappedBooking);

            Storage::put($exportFilename, $csvWriter->toString());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
