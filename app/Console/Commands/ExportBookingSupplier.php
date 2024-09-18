<?php

namespace App\Console\Commands;

use App\Models\ApiBookingItem;
use App\Models\ApiBookingsMetadata;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
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
                ->whereNotNull('supplier')
                ->get();

            foreach ($bookings as $booking) {
                $csvWriter->insertOne([
                    $booking['booking_item'],
                    $booking['booking_supplier'],
                ]);
            }

            Storage::put($exportFilename, $csvWriter->toString());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
