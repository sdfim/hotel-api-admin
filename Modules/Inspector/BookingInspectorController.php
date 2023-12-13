<?php

namespace Modules\Inspector;

use App\Repositories\ChannelRenository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\ApiBookingInspector;

class BookingInspectorController extends BaseInspectorController
{
    /**
     * @param $booking_id
     * @param $query
     * @param $content
     * @param $client_content
     * @param $supplier_id
     * @param $type
     * @param $subType
     * @param $search_type
     * @return string|bool
     */
    public function save($booking_id, $query, $content, $client_content, $supplier_id, $type, $subType, $search_type): string|bool
    {

		Log::debug('BookingInspectorController save query: ', [
			'query' => $query,
		]);
        try {
            $this->current_time = microtime(true);

            $token_id = ChannelRenository::getTokenId(request()->bearerToken());
            $search_id = $query['search_id'];
			$booking_item = $query['booking_item'] ?? null;
            $query = json_encode($query);
            $content = json_encode($content);
            $client_content = json_encode($client_content);
            $hash = md5($query . $booking_id);

			$generalPath = self::PATH_INSPECTORS . 'booking_inspector/' . date("Y-m-d") . '/' . $type . '_' . $subType . '_' . $hash;
            $path = $generalPath . '.json';
            $client_path = $generalPath . '.client.json';

            $booking = ApiBookingInspector::where('response_path', $path)->first();
            if (!$booking) {
				Storage::put($path, $content);
				Log::debug('BookingInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

				Storage::put($client_path, $client_content);
				Log::debug('BookingInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');
			}

            $data = [
                'booking_id' => $booking_id,
                'token_id' => $token_id,
                'supplier_id' => $supplier_id,
                'search_id' => $search_id,
				'booking_item' => $booking_item,
                'search_type' => $search_type,
                'type' => $type,
                'sub_type' => $subType,
                'request' => $query,
                'response_path' => $path,
                'client_response_path' => $client_path,
            ];

			Log::debug('BookingInspectorController save data: ', $data);

            $booking = ApiBookingInspector::create($data);
            Log::debug('BookingInspectorController save to DB: ' . $this->executionTime() . ' seconds');

            return $booking ? $booking->id : false;

        } catch (\Exception $e) {
            Log::error('Error save ApiSearchInspector: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());

            return false;
        }
    }
}
