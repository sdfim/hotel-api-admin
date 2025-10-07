<?php

namespace Modules\Inspector;

use App\Enums\BookingStatusEnum;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use App\Models\Channel;
use App\Repositories\ApiBookingInspectorRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookingInspectorController extends BaseInspectorController
{
    public function save(array $inspector, array $content, array $client_content): string|bool
    {
        try {
            $this->current_time = microtime(true);

            $generalPath = self::PATH_INSPECTORS.'booking_inspector/'.date('Y-m-d').'/'.$inspector['type']
                .'_'.$inspector['sub_type'].'_'.$inspector['booking_item'].'__'.time();
            $path = $generalPath.'.json';
            $client_path = $generalPath.'.client.json';

            $original = null;
            if (! $content instanceof \stdClass) {
                if (isset($content['original'])) {
                    $original = $content['original'];
                    unset($content['original']);
                    $original = is_array($original) ? json_encode($original) : $original;
                }
            }

            $inspector['response_path'] = '';
            $inspector['client_response_path'] = '';

            $booking = ApiBookingInspector::where('response_path', $path)->first();
            if (! $booking) {
                Storage::put($path, json_encode($content));
                Log::debug('BookingInspectorController save to Storage: '.$this->executionTime().' seconds');

                Storage::put($client_path, json_encode($client_content));
                Log::debug('BookingInspectorController save client_response to Storage: '.$this->executionTime().' seconds');

                $inspector['response_path'] = $path;
                $inspector['client_response_path'] = $client_path;
            }

            if ($original) {
                $original_path = $generalPath.'.original.json';
                Storage::put($original_path, $original);
                Log::debug('BookingInspectorController save original to Storage: '.$this->executionTime().' seconds');
            }

            $inspector['request'] = json_encode($inspector['request']);
            $inspector['status_describe'] = json_encode($inspector['status_describe']);

            Log::debug('BookingInspectorController save data: ', $inspector);

            // UI token_id
            if (! isset($inspector['token_id'])) {
                $inspector['token_id'] = Channel::where('name', 'UI Channel')->first()?->token_id;
            }

            $booking = ApiBookingInspector::create($inspector);

            Log::debug('BookingInspectorController save to DB: '.$this->executionTime().' seconds');

            // Update ApiBookingsMetadata if type is 'book' and sub_type is 'retrieve'
            if (
                isset($inspector['type'], $inspector['sub_type'], $inspector['booking_id'], $inspector['booking_item']) &&
                $inspector['type'] === 'book' && $inspector['sub_type'] === 'retrieve'
            ) {
                $isBook = ApiBookingInspectorRepository::isBook($inspector['booking_id'], $inspector['booking_item']);
                $isCancel = ApiBookingInspectorRepository::isCancel($inspector['booking_item']);
                $status = $isCancel ? BookingStatusEnum::CANCELED : ($isBook ? BookingStatusEnum::BOOKED : 'other');

                $metadata = ApiBookingsMetadata::where('booking_id', $inspector['booking_id'])
                    ->where('booking_item', $inspector['booking_item'])
                    ->first();
                if ($metadata) {
                    $metadata->status = $status;
                    $metadata->retrieve = $client_content;
                    $metadata->updated_at = $booking->created_at;
                    $metadata->save();
                    Log::debug('ApiBookingsMetadata updated for book/retrieve', [
                        'booking_id' => $inspector['booking_id'],
                        'booking_item' => $inspector['booking_item'],
                    ]);
                }
            }

            return $booking->id;

        } catch (Exception $e) {
            Log::error('BookingInspectorController Error save ApiBookingInspector: '.$e->getMessage().' | '.$e->getLine().' | '.$e->getFile());
            Log::error($e->getTraceAsString());
        }

        return false;
    }
}
