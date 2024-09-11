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

            if (is_string($content)) {
                // Split the original string into two parts (HBSI and Expedia)
                $parts = explode('"Expedia_', $content, 2);
                // Perform replacements only on the second part  (Expedia) if it exists
                if (isset($parts[1])) {
                    $parts[1] = str_replace('\"', '"', $parts[1]);
                    $parts[1] = str_replace('"{', '{', $parts[1]);
                    $parts[1] = str_replace('}"', '}', $parts[1]);
                }
                // Concatenate the parts back together
                if (isset($parts[1])) $content = $parts[0] . '"Expedia_' . ($parts[1] ?? '');
                else $content = $parts[0];
            }

            if (is_string($original)) {
                // Split the original string into two parts (HBSI and Expedia)
                $parts = explode('"Expedia_', $original, 2);
                // Perform replacements only on the second part  (Expedia) if it exists
                if (isset($parts[1])) {
                    $parts[1] = str_replace('\"', '"', $parts[1]);
                    $parts[1] = str_replace('"{', '{', $parts[1]);
                    $parts[1] = str_replace('}"', '}', $parts[1]);
                }
                // Concatenate the parts back together
                if (isset($parts[1])) $original = $parts[0] . '"Expedia_' . ($parts[1] ?? '');
                else $original = $parts[0];
            }

            $clientContent = is_array($clientContent) ? json_encode($clientContent) : $clientContent;

            $generalPath = self::PATH_INSPECTORS.'search_inspector/'.date('Y-m-d').'/'.$inspector['type'].'_'.$inspector['search_id'];
            $path = $generalPath.'.json';
            $client_path = $generalPath.'.client.json';
            $original_path = $generalPath.'.original.json';

            $inspectorPath = ApiSearchInspector::where('response_path', $path)?->first();
            // check if inspector not exists
            if (! $inspectorPath) {
                Storage::put($path, $content);
                Log::debug('SearchInspectorController save to Storage: '.$this->executionTime().' seconds');

                Storage::put($client_path, $clientContent);
                Log::debug('SearchInspectorController save client_response to Storage: '.$this->executionTime().' seconds');

                Storage::put($original_path, $original);
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
}
