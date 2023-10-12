<?php

namespace Modules\Inspector;

use Illuminate\Support\Facades\Storage;
use App\Models\Channels;
use App\Models\ApiBookingInspector;
use Modules\Inspector\BaseInspectorController;
class BookingInspectorController extends BaseInspectorController
{
	public function save($booking_id, $query, $content, $client_content, $supplier_id, $type = 'add_item', $subType = 'main') : string|bool
	{
		try {
			$this->current_time = microtime(true);

			$ch = new Channels;
			$token_id = $ch->getTokenId(request()->bearerToken());
			$search_id = $query['search_id'];
			$query = json_encode($query);
			$content = json_encode($content);
			$client_content = json_encode($client_content);
			$hash = md5($query.$booking_id);

			$path = $type . '/' . date("Y-m-d") . '/' . $subType . '/' . $hash.'.json';
			$client_path = $type . '/' . date("Y-m-d") . '/' . $subType . '/' . $hash.'.client.json';

			$booking = ApiBookingInspector::where('response_path', $path)->first();
			if ($booking) return $booking->id;
			\Log::debug('BookingInspectorController item exist: ' . $this->executionTime() . ' seconds');

			Storage::put($path, $content);
			\Log::debug('BookingInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

			Storage::put($client_path, $client_content);
			\Log::debug('BookingInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');

			$data = [
				'booking_id' => $booking_id,
				'token_id' => $token_id,
				'supplier_id' => $supplier_id,
				'search_id' => $search_id,
				'type' => $type,
				'sub_type' => $subType, 
				'request' => $query,
				'response_path' => $path,
				'client_response_path' => $client_path,
			];

			$bokking = ApiBookingInspector::create($data);
			\Log::debug('BookingInspectorController save to DB: ' . $this->executionTime() . ' seconds');

			return $bokking ? $bokking->id : false;

		} catch (\Exception $e) {
            \Log::error('Error save ApiSearchInspector: ' . $e->getMessage(). ' | ' . $e->getLine() . ' | ' . $e->getFile());
			
			return false;
		}
	}

}