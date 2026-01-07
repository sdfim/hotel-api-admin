<?php

namespace Modules\API\Suppliers\Base\Traits;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use Exception;
use Illuminate\Support\Arr;
use Modules\Enums\SupplierNameEnum;

trait HotelBookingavCancelTrait
{
    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, SupplierNameEnum $supplier, int $iterations = 0): ?array
    {
        $booking_id = $filters['booking_id'];

        $supplierId = Supplier::where('name', $supplier->value)->first()->id;
        $inspectorCancel = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id,
            $filters,
            $supplierId,
            'cancel_booking',
            'true',
            'hotel',
        ]);

        if ($this->client) {
            try {
                $cancelData = $this->client->cancel(
                    $apiBookingsMetadata,
                    $inspectorCancel
                );

                $dataResponseToSave['original'] = [
                    'request' => $cancelData['request'] ?? [],
                    'response' => $cancelData['response'] ?? [],
                ];

                if (Arr::get($cancelData, 'errors')) {
                    $res = Arr::get($cancelData, 'errors');
                } else {
                    $res = [
                        'booking_item' => $apiBookingsMetadata->booking_item,
                        'status' => 'Room canceled.',
                    ];

                    SaveBookingInspector::dispatch($inspectorCancel, $dataResponseToSave, $res);
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $res = [
                    'booking_item' => $apiBookingsMetadata->booking_item,
                    'status' => $message,
                    'Error' => $message,
                ];

                SaveBookingInspector::dispatch(
                    $inspectorCancel,
                    [],
                    $res,
                    'error',
                    ['side' => 'app', 'message' => $message]
                );
            }

            return $res;
        }

        return null;
    }
}
