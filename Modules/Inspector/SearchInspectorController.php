<?php

namespace Modules\Inspector;

use App\Repositories\ChannelRenository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Channel;
use App\Models\ApiSearchInspector;

class SearchInspectorController extends BaseInspectorController
{
    /**
     * @param $search_id
     * @param $query
     * @param $content
     * @param $clientContent
     * @param $suppliers
     * @param $type
     * @param $search_type
     * @return string|bool
     */
    public function save($search_id, $query, $content, $clientContent, $suppliers, $type, $search_type): string|bool
    {
        try {
            $this->current_time = microtime(true);

            $token_id = ChannelRenository::getTokenId(request()->bearerToken());
            $query = json_encode($query);
            $content = json_encode($content);
            $clientContent = json_encode($clientContent);
            $hash = md5($query);

            $generalPath = self::PATH_INSPECTORS . 'search_inspector/' . date("Y-m-d") . '/' . $type . '_' . $hash;
            $path = $generalPath . '.json';
            $client_path = $generalPath . '.client.json';

            $inspector = ApiSearchInspector::where('response_path', $path)->first();
            // check if inspector not exists
            if (!$inspector) {
                Storage::put($path, $content);
                Log::debug('SearchInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($client_path, $clientContent);
                Log::debug('SearchInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');
            }

            $data = [
                'search_id' => $search_id,
                'token_id' => $token_id,
                'suppliers' => implode(',', $suppliers),
                'search_type' => $search_type,
                'type' => $type,
                'request' => $query,
                'response_path' => $path,
                'client_response_path' => $client_path,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $inspector = ApiSearchInspector::insert($data);
            Log::debug('SearchInspectorController save to DB: ' . $this->executionTime() . ' seconds');

            return (bool)$inspector;

        } catch (\Exception $e) {
            Log::error('Error save ApiSearchInspector: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());

            return false;
        }
    }
}
