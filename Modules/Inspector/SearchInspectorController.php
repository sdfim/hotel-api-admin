<?php

namespace Modules\Inspector;

use Illuminate\Support\Facades\Storage;
use App\Models\Channels;
use App\Models\ApiSearchInspector;
use Modules\Inspector\BaseInspectorController;
class SearchInspectorController extends BaseInspectorController
{
	public function save($query, $content, $clientContent, $suppliers , $type = 'search') : string|bool
	{
		try {
			$this->current_time = microtime(true);

			$ch = new Channels;
			$token_id = $ch->getTokenId(request()->bearerToken());
			$query = json_encode($query);
			$content = json_encode($content);
			$clientContent = json_encode($clientContent);
			$hash = md5($query);
			$path = $type . '/' . date("Y-m-d") . '/' . $hash.'.json';
			$client_path = $type . '/' . date("Y-m-d") . '/' . $hash.'_client.json';

			$inspector = ApiSearchInspector::where('response_path', $path)->first();
			if ($inspector) return $inspector->id;
			\Log::debug('SearchInspectorController search exist: ' . $this->executionTime() . ' seconds');

			Storage::put($path, $content);
			\Log::debug('SearchInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

			Storage::put($client_path, $clientContent);
			\Log::debug('SearchInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');

			$data = [
				'token_id' => $token_id,
				'suppliers' => implode(',', $suppliers),
				'type' => $type,
				'request' => $query,
				'response_path' => $path,
				'client_response_path' => $client_path,
			];

			$inspector = ApiSearchInspector::create($data);
			\Log::debug('SearchInspectorController save to DB: ' . $this->executionTime() . ' seconds');

			return $inspector ? $inspector->id : false;

		} catch (\Exception $e) {
            \Log::error('Error save ApiSearchInspector: ' . $e->getMessage(). ' | ' . $e->getLine() . ' | ' . $e->getFile());
			
			return false;
		}
	}

}