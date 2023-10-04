<?php

namespace Modules\Inspector;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Storage;
use App\Models\Channels;
use App\Models\ApiInspector;
use Illuminate\Support\Str;
class InspectorController
{
	public function save($query, $content, $supplier_id , $type = 'search') : string|bool
	{
		try {
			$ch = new Channels;
			$token_id = $ch->getTokenId(request()->bearerToken());
			$query = json_encode($query);
			$content = json_encode($content);
			$hash = md5($query);
			$path = $type. '/' . $hash.'.json';

			$inspector = ApiInspector::where('response_path', $path)->first();
			if ($inspector) return $inspector->id;

			Storage::put($path, $content);

			$uuid = Str::uuid()->toString();

			$data = [
				'id' => $uuid,
				'token_id' => $token_id,
				'supplier_id' => $supplier_id,
				'type' => $type,
				'request' => $query,
				'response_path' => $path
			];

			$inspector = ApiInspector::create($data);

			return $inspector ? $uuid : false;

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