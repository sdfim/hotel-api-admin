<?php

namespace Modules\Inspector;

use App\Models\ApiSearchInspector;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Opcodes\LogViewer\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class SearchInspectorController extends BaseInspectorController
{
    /**
     * @param array $data
     * @return string|bool
     * @throws InvalidArgumentException
     */
    public function save(array $data): string|bool
    {
        /**
         * @param  array  $inspector
         * @param  array  $original
         * @param  array  $content
         * @param  array  $clientContent
         */
        [$inspector, $original, $content, $clientContent] = $data;

        try {
            $this->current_time = microtime(true);

            if (isset($original['keyCache'])) {
                $keys = $original['keyCache'];
                $original = Cache::get($keys['dataOriginal']);
                $content = Cache::get($keys['content']);
                $clientContent = Cache::get($keys['clientContent']);

                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }

            $content = $this->processString($content);
            $original = $this->processString($original);
            $clientContent = is_array($clientContent) ? json_encode($clientContent) : $clientContent;

            $generalPath = self::PATH_INSPECTORS.'search_inspector/'.date('Y-m-d').'/'.$inspector['type'].'_'.$inspector['search_id'];
            $path = $generalPath.'.json';
            $client_path = $generalPath.'.client.json';
            $original_path = $generalPath.'.original.json';

            $inspectorPath = ApiSearchInspector::where('response_path', $path)?->first();
            // check if inspector not exists
            if (! $inspectorPath) {
                $prepareContent = function ($content) {
                    return is_array($content) ? json_encode($content) : $content;
                };

                Storage::put($path, $prepareContent($content));
                Log::debug('SearchInspectorController save to Storage: '.$this->executionTime().' seconds');

                Storage::put($client_path, $prepareContent($clientContent));
                Log::debug('SearchInspectorController save client_response to Storage: '.$this->executionTime().' seconds');

                Storage::put($original_path, $prepareContent($original));
                Log::debug('SearchInspectorController save original to Storage: '.$this->executionTime().' seconds');

                $inspector['client_response_path'] = $client_path;
                $inspector['original_path'] = $original_path;
                $inspector['response_path'] = $path;
            }

            $inspector['status_describe'] = json_encode($inspector['status_describe']);

            $inspector = ApiSearchInspector::updateOrCreate(
                ['search_id' => $inspector['search_id']], $inspector);

            Log::debug('SearchInspectorController save to DB: '.$this->executionTime().' seconds');

            return (bool) $inspector;

        } catch (Exception $e) {
            Log::error('Error save ApiSearchInspector: '.$e->getMessage().' | '.$e->getLine().' | '.$e->getFile());
            Log::error($e->getTraceAsString());

            return false;
        }
    }

    private function processString($input): string
    {
        if (!is_string($input)) {
            return $input;
        }

        // Split the string into parts based on Expedia_ and HBSI
        $expediaParts = explode('"Expedia_', $input);
        $result = $expediaParts[0]; // Start with the first part before any Expedia_ block

        for ($i = 1; $i < count($expediaParts); $i++) {
            // Check if the current part contains HBSI
            $subParts = explode('"HBSI', $expediaParts[$i], 2);

            // Apply replacements only to the Expedia_ part before HBSI
            $subParts[0] = str_replace(['\"', '\\\\"', '"{', '}"'], ['"', '\"', '{', '}'], $subParts[0]);

            // Reconstruct the part
            $result .= '"Expedia_' . implode('"HBSI', $subParts);
        }

        return $result;
    }
}
