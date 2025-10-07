<?php

namespace App\Console\Commands;

use App\Enums\BookingStatusEnum;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Enums\InspectorStatusEnum;

class UpdateApiBookingsMetadataFromInspector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-api-bookings-metadata-from-inspector';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
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

        $updated = 0;
        foreach ($retrieved as $item) {
            $isBook = ApiBookingInspectorRepository::isBook($item->booking_id, $item->booking_item);
            $isCancel = ApiBookingInspectorRepository::isCancel($item->booking_item);
            $status = $isCancel ? BookingStatusEnum::CANCELED : ($isBook ? BookingStatusEnum::BOOKED : 'other');
            $updated_at = $item->created_at;

            $jsonRaw = Storage::get($item->client_response_path);
            $metadata = ApiBookingsMetadata::where('booking_id', $item->booking_id)
                ->where('booking_item', $item->booking_item)
                ->first();
            if ($metadata) {
                $metadata->status = $status;
                $metadata->retrieve = json_decode($jsonRaw, true);
                $metadata->updated_at = $updated_at;
                $metadata->save();
                $updated++;
            }
        }
        $this->info("Updated $updated ApiBookingsMetadata records.");
    }
}
