<?php

namespace Modules\Inspector;

use Illuminate\Support\Facades\Storage;
use App\Models\Channels;
use App\Models\ApiInspector;
use Modules\Inspector\BaseInspectorController;
class InspectorController extends BaseInspectorController
{
	public function save($query, $content, $supplier_id , $type = 'search') : string|bool
	{
		try {
			$this->current_time = microtime(true);

			$ch = new Channels;
			$token_id = $ch->getTokenId(request()->bearerToken());
			$query = json_encode($query);
			$content = json_encode($content);
			$hash = md5($query);
			$path = $type. '/' . $hash.'.json';

			$inspector = ApiInspector::where('response_path', $path)->first();
			if ($inspector) return $inspector->id;
			\Log::debug('InspectorController search exist: ' . $this->executionTime() . ' seconds');

			Storage::put($path, $content);
			\Log::debug('InspectorController save to Storage: ' . $this->executionTime() . ' seconds');


			$data = [
				'token_id' => $token_id,
				'supplier_id' => $supplier_id,
				'type' => $type,
				'request' => $query,
				'response_path' => $path
			];

			$inspector = ApiInspector::create($data);
			\Log::debug('InspectorController save to DB: ' . $this->executionTime() . ' seconds');

			return $inspector ? $inspector->id : false;

		} catch (\Exception $e) {
            \Log::error('Error save ApiInspector: ' . $e->getMessage(). ' | ' . $e->getLine() . ' | ' . $e->getFile());
			
			return false;
		}
	}

	public function get()
	{
		//
	}

	public function delete()
	{
		//
	}

	public function update()
	{
		//
	}

}