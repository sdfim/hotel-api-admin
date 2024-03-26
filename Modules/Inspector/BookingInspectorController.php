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
    /**
     * @param $data
     * @return string|bool
     */
    public function save($data): string|bool
    {
        /**
         * @param string $booking_id
         * @param array $query
         * @param array $content
         * @param array $client_content
         * @param int $supplier_id
         * @param string $type
         * @param string $subType
         * @param string $search_type
         */
        [$booking_id, $query, $content, $client_content, $supplier_id, $type, $subType, $search_type] = $data;

        Log::debug('BookingInspectorController save query: ', [
            'query' => $query,
        ]);
        try {
            $this->current_time = microtime(true);

            $token_id = ChannelRenository::getTokenId(request()->bearerToken());
            $booking_item = $query['booking_item'] ?? null;
            $search_id = $query['search_id'] ?? $booking_item
                ? ApiBookingItem::where('booking_item', $booking_item)->first()->search_id
                : null;

            $query = json_encode($query);

            $original = null;
            if (!$content instanceof \stdClass) {
                if (isset($content['original'])) {
                    $original = $content['original'];
                    unset($content['original']);
                    $original = is_array($original) ? json_encode($original) : $original;
                }
            }
            $content = json_encode($content);
            $client_content = json_encode($client_content);

            $generalPath = self::PATH_INSPECTORS . 'booking_inspector/' . date("Y-m-d") . '/' . $type . '_' . $subType . '_' . $booking_item;
            $path = $generalPath . '.json';
            $client_path = $generalPath . '.client.json';

            $booking = ApiBookingInspector::where('response_path', $path)->first();
            if (!$booking) {
                Storage::put($path, $content);
                Log::debug('BookingInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($client_path, $client_content);
                Log::debug('BookingInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');

                if ($original) {
                    $original_path = $generalPath . '.original.json';
                    Storage::put($original_path, $original);
                    Log::debug('BookingInspectorController save original to Storage: ' . $this->executionTime() . ' seconds');
                }
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

        } catch (Exception $e) {
            Log::error('Error save ApiSearchInspector: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());

            return false;
        }
    }
}
