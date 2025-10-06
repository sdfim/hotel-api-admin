<?php

namespace App\Console\Commands\Tools;

use App\Models\ApiBookingInspector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Requests\BookingRetrieveItemsRequest;
use Modules\Enums\InspectorStatusEnum;

class RetrieveMissingBookings extends Command
{
    protected $signature = 'bookings:retrieve-missing';

    protected $description = 'Retrieve bookings with missing or invalid client_response_path files';

    public function handle()
    {
        $retrieved = ApiBookingInspector::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('api_booking_inspector')
                ->where('type', 'book')
                ->where('sub_type', 'retrieve')
                ->where('status', '!=', InspectorStatusEnum::ERROR->value)
                ->groupBy('booking_id', 'booking_item');
        })
            ->orderByDesc('id')
            ->get();

        $disk = config('filesystems.default', 's3');
        foreach ($retrieved as $item) {
            $jsonRaw = null;
            try {
                $jsonRaw = Storage::disk($disk)->get($item->client_response_path);
            } catch (\Exception $e) {
                // File not found or error
            }
            if (! $jsonRaw) {
                $request = new BookingRetrieveItemsRequest([
                    'booking_id' => $item->booking_id,
                ]);
                app(BookApiHandler::class)->retrieveBooking($request);
                $this->info('Missing file for booking_id: '.$item->booking_id.', booking_item: '.$item->booking_item);
                continue;
            }
            $json = json_decode($jsonRaw, true);
            if (! $json) {
                $request = new BookingRetrieveItemsRequest([
                    'booking_id' => $item->booking_id,
                ]);
                app(BookApiHandler::class)->retrieveBooking($request);
                $this->info('Invalid JSON for booking_id: '.$item->booking_id.', booking_item: '.$item->booking_item);
                continue;
            }
        }
        $this->info('Completed retrieval of missing/invalid bookings.');
    }
}
