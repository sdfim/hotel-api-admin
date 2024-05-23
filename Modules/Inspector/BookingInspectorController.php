<?php

namespace Modules\Inspector;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Repositories\ChannelRenository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookingInspectorController extends BaseInspectorController
{
    public function save(array $inspector, array $content, array $client_content): string|bool
    {
        try {
            $this->current_time = microtime(true);

            $generalPath = self::PATH_INSPECTORS . 'booking_inspector/' . date("Y-m-d") . '/' . $inspector['type']
                . '_' . $inspector['sub_type'] . '_' . $inspector['booking_item'] . '__' . $inspector['booking_id'];
            $path = $generalPath . '.json';
            $client_path = $generalPath . '.client.json';

            $inspector['response_path'] = '';
            $inspector['client_response_path'] = '';

            $booking = ApiBookingInspector::where('response_path', $path)->first();
            if (!$booking) {
                Storage::put($path, json_encode($content));
                Log::debug('BookingInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($client_path, json_encode($client_content));
                Log::debug('BookingInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');

                $inspector['response_path'] = $path;
                $inspector['client_response_path'] = $client_path;
            }

            $inspector['request'] = json_encode($inspector['request']);
            $inspector['status_describe'] = json_encode($inspector['status_describe']);

            Log::debug('BookingInspectorController save data: ', $inspector);

            $booking = ApiBookingInspector::create($inspector);

            Log::debug('BookingInspectorController save to DB: ' . $this->executionTime() . ' seconds');

            return $booking->id;

        } catch (Exception $e) {
            Log::error('Error save ApiSearchInspector: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return false;
        }
    }
}
