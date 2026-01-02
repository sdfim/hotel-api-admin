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

            $clientContentWithPricingRules = '';
            if (isset($original['keyCache'])) {
                $keys = $original['keyCache'];

                $original = Cache::get($keys['dataOriginal']);
                $content = Cache::get($keys['content']);
                $clientContent = Cache::get($keys['clientContent']);
                $clientContentWithPricingRules = Cache::get($keys['clientContentWithPricingRules']);

                $original = gzuncompress($original);
                $content = gzuncompress($content);
                $clientContent = gzuncompress($clientContent);
                $clientContentWithPricingRules = gzuncompress($clientContentWithPricingRules);

                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }

            $original = is_array($original) ? json_encode($original) : $original;
            $content = is_array($content) ? json_encode($content) : $content;

            $clientContent = is_array($clientContent) ? json_encode($clientContent) : $clientContent;
            $clientContentWithPricingRules = is_array($clientContentWithPricingRules)
                ? json_encode($clientContentWithPricingRules)
                : $clientContentWithPricingRules;

            $generalPath = self::PATH_INSPECTORS.'search_inspector/'.date('Y-m-d').'/'.$inspector['type'].'_'.$inspector['search_id'];
            $path = $generalPath.'.json';
            $client_path = $generalPath.'.client.json';
            $client_path_with_pr = $generalPath.'.client_with_pracing_rule_applier.json';
            $original_path = $generalPath.'.original.json';

            $inspectorPath = ApiSearchInspector::where('response_path', $path)?->first();
            // check if inspector not exists
            if (! $inspectorPath) {
                $prepareContent = function ($content) {
                    return is_array($content) ? json_encode($content) : $content;
                };

                Storage::put($path, $prepareContent($content));
                Log::debug('SearchInspectorController save to Storage: '.$this->executionTime().' seconds', ['path' => $path, 'size' => strlen($content)]);

                Storage::put($client_path, $prepareContent($clientContent));
                Log::debug('SearchInspectorController save client_response to Storage: '.$this->executionTime().' seconds', ['path' => $client_path, 'size' => strlen($clientContent)]);

                Storage::put($client_path_with_pr, $prepareContent($clientContentWithPricingRules));
                Log::debug('SearchInspectorController save client_response_with_pr to Storage: '.$this->executionTime().' seconds', ['path' => $client_path_with_pr, 'size' => strlen($clientContentWithPricingRules)]);

                Storage::put($original_path, $prepareContent($original));
                Log::debug('SearchInspectorController save original to Storage: '.$this->executionTime().' seconds', ['path' => $original_path, 'size' => strlen($original)]);

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

            throw $e;
        }
    }
}
