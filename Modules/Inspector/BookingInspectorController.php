<?php

namespace Modules\Inspector;

use Illuminate\Support\Facades\Storage;
use App\Models\Channels;
use App\Models\ApiBookingInspector;
use Modules\Inspector\BaseInspectorController;
class BookingInspectorController extends BaseInspectorController
{
	public function save($booking_id, $query, $content, $supplier_id, $type = 'add_item', $subType = 'main') : string|bool
	{
		try {
			$this->current_time = microtime(true);

			$ch = new Channels;
			$token_id = $ch->getTokenId(request()->bearerToken());
			$earch_id = $query['inspector'];
			$query = json_encode($query);
			$content = json_encode($content);
			$hash = md5($query);
			$path = $type . '/' . date("Y-m-d") . '/' . $subType . '/' . $hash.'.json';

			$bokking = ApiBookingInspector::where('response_path', $path)->first();
			if ($bokking) return $bokking->id;
			\Log::debug('BookingInspectorController item exist: ' . $this->executionTime() . ' seconds');

			Storage::put($path, $content);
			\Log::debug('BookingInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

			$data = [
				'booking_id' => $booking_id,
				'token_id' => $token_id,
				'supplier_id' => $supplier_id,
				'search_id' => $earch_id,
				'type' => $type,
				'sub_type' => $subType, 
				'request' => $query,
				'response_path' => $path,
				'client_response_path' => '',
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