<?php

namespace Modules\Inspector;

use App\Models\ApiSearchInspector;
use App\Repositories\ChannelRenository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SearchInspectorController extends BaseInspectorController
{
    /**
     * @param array $data
     * @return string|bool
     */
    public function save(array $data): string|bool
    {
        /**
         * @param string $search_id
         * @param array $query
         * @param array $original
         * @param array $content
         * @param array $clientContent
         * @param array $suppliers
         * @param string $type
         * @param string $search_type
         */
        [$search_id, $query, $original, $content, $clientContent, $suppliers, $type, $search_type] = $data;

        try {
            $this->current_time = microtime(true);

            $token_id = ChannelRenository::getTokenId(request()->bearerToken());
            $original = is_array($original) ? json_encode($original) : $original;
            $query = json_encode($query);
            $content = is_array($content) ? json_encode($content) : $content;
            $clientContent = json_encode($clientContent);

            $generalPath = self::PATH_INSPECTORS . 'search_inspector/' . date("Y-m-d") . '/' . $type . '_' . $search_id;
            $path = $generalPath . '.json';
            $client_path = $generalPath . '.client.json';
            $original_path = $generalPath . '.original.json';

            $inspector = ApiSearchInspector::where('response_path', $path)->first();
            // check if inspector not exists
            if (!$inspector) {
                Storage::put($path, $content);
                Log::debug('SearchInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($client_path, $clientContent);
                Log::debug('SearchInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($original_path, $original);
                Log::debug('SearchInspectorController save original to Storage: ' . $this->executionTime() . ' seconds');
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
                'original_path' => $original_path,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $inspector = ApiSearchInspector::insert($data);
            Log::debug('SearchInspectorController save to DB: ' . $this->executionTime() . ' seconds');

            return (bool)$inspector;

        } catch (Exception $e) {
            Log::error('Error save ApiSearchInspector: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());

            return false;
        }
    }
}
