<?php

namespace App\Console\Commands;

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

class ImportTravelTekBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:traveltek-bookings';

    protected $description = 'Sync TravelTek Bookings into DataBase';

    protected const BATCH = 100;

    // variables to validate uuid with external ids
    private $bookingIds = [];

    private $bookingItemIds = [];

    /**
     * @throws Exception
     */
    public function handle()
    {
        $timeStamp = Carbon::now();

        // Import vars
        $filename = 'imports/traveltek_hbsi.csv';
        $tempImportFilePath = tempnam(sys_get_temp_dir(), 'csv_import_');

        // Export vars
        $exportFilename = 'exports/traveltek_hbsi.csv';
        $csvWriter = Writer::createFromString();

        $csvWriter->insertOne([
            'Portfolio',
            'Hotel Id',
            'OBE Booking',
            'OBE Item',
        ]);

        if (Storage::exists($filename)) {
            Storage::disk('local')->put($tempImportFilePath, Storage::get($filename));

            try {
                DB::transaction(function () use ($tempImportFilePath, $timeStamp, $csvWriter) {
                    foreach ($this->readCsvRowsInChunks($tempImportFilePath, self::BATCH) as $chunk) {
                        foreach ($chunk as $row) {
                            $bookingReference = Arr::get($row, 'Booking Reference');
                            $bookingItemReference = Arr::get($row, 'Reference');
                            $hotelSupplierId = Arr::get($row, 'itemcode');

                            $bookingId = $this->getUuidFromExternalId($bookingReference, 'booking');
                            $bookingItem = $this->getUuidFromExternalId($bookingItemReference, 'booking_item');

                            if ($bookingItem === null) {
                                continue;
                            }

                            $reservation = [
                                'bookingId' => Arr::get($row, 'Reference'),
                                'main_guest' => [
                                    'GivenName' => Arr::get($row, 'First Name'),
                                    'Surname' => Arr::get($row, 'Last Name'),
                                ],
                                'ReservationId' => $bookingItemReference,
                                'type' => 8,
                            ];

                            $prevRecord = ApiBookingsMetadata::where('supplier_id', 2)
                                ->where('supplier_booking_item_id', $bookingItemReference)
                                ->first();

                            if ($prevRecord !== null) {
                                continue;
                            }

                            ApiBookingsMetadata::insert([
                                'booking_item' => $bookingItem,
                                'booking_id' => $bookingId,
                                'supplier_id' => 2,
                                'supplier_booking_item_id' => $bookingItemReference,
                                'hotel_supplier_id' => $hotelSupplierId,
                                'booking_item_data' => json_encode($reservation),
                                'created_at' => $timeStamp,
                                'updated_at' => $timeStamp,
                            ]);

                            $csvWriter->insertOne([
                                $bookingReference,
                                Arr::get($row, 'Hotel ID'),
                                $bookingId,
                                $bookingItem,
                            ]);
                        }
                    }
                });

                Storage::put($exportFilename, $csvWriter->toString());
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }

            Storage::delete($tempImportFilePath);
        }

    }

    private function getUuidFromExternalId($externalId, $type): ?string
    {
        if ($type === 'booking') {
            $bookingId = Arr::get($this->bookingIds, $externalId);

            if ($bookingId === null) {
                $bookingId = Str::uuid()->toString();

                $this->bookingIds[$externalId] = $bookingId;
            }

            return $bookingId;
        } else {
            if (! in_array($externalId, $this->bookingItemIds)) {
                $this->bookingItemIds[] = $externalId;

                return Str::uuid()->toString();
            }

            return null;
        }
    }

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    private function readCsvRowsInChunks($filePath, $chunkSize = 100)
    {
        // Initialize the CSV reader
        $csvReader = Reader::createFromPath(storage_path("app$filePath"));

        // Iterate over the CSV rows in chunks
        $csvReader->setHeaderOffset(0); // If CSV file has header, adjust this value accordingly
        $header = null;
        $chunk = [];
        foreach ($csvReader as $row) {
            if ($header === null) {
                $header = array_keys($row);
            }

            $chunk[] = array_combine($header, $row);

            if (count($chunk) === $chunkSize) {
                yield $chunk; // Yield the chunk
                $chunk = [];
            }
        }

        // Yield the remaining chunk
        if (! empty($chunk)) {
            yield $chunk;
        }
    }
}
