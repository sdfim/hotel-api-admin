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
         * @param array $inspector
         * @param array $original
         * @param array $content
         * @param array $clientContent
         */
        [$inspector, $original, $content, $clientContent] = $data;

        try {
            $this->current_time = microtime(true);

            $original = is_array($original) ? json_encode($original) : $original;
            $content = is_array($content) ? json_encode($content) : $content;
            $clientContent = json_encode($clientContent);

            $generalPath = self::PATH_INSPECTORS . 'search_inspector/' . date("Y-m-d") . '/' . $inspector['type'] . '_' . $inspector['search_id'];
            $path = $generalPath . '.json';
            $client_path = $generalPath . '.client.json';
            $original_path = $generalPath . '.original.json';

            $inspectorPath = ApiSearchInspector::where('response_path', $path)?->first();
            // check if inspector not exists
            if (!$inspectorPath) {
                Storage::put($path, $content);
                Log::debug('SearchInspectorController save to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($client_path, $clientContent);
                Log::debug('SearchInspectorController save client_response to Storage: ' . $this->executionTime() . ' seconds');

                Storage::put($original_path, $original);
                Log::debug('SearchInspectorController save original to Storage: ' . $this->executionTime() . ' seconds');

                $inspector['client_response_path'] = $client_path;
                $inspector['original_path'] = $original_path;
                $inspector['response_path'] = $path;
            }

            $inspector['status_describe'] = json_encode($inspector['status_describe']);

            $inspector = ApiSearchInspector::updateOrCreate(
                ['search_id' => $inspector['search_id']], $inspector);

            Log::debug('SearchInspectorController save to DB: ' . $this->executionTime() . ' seconds');

            return (bool)$inspector;

        } catch (Exception $e) {
            Log::error('Error save ApiSearchInspector: ' . $e->getMessage() . ' | ' . $e->getLine() . ' | ' . $e->getFile());
            Log::error($e->getTraceAsString());

            return false;
        }
    }
}
