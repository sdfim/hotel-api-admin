<?php

namespace App\Console\Commands\Assistants;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use OpenAI\Laravel\Facades\OpenAI;

class MergeSupplierDataOpenAi extends Command
{
    protected $signature = 'merge:suppliers:openai-provider {supplierData} {giata_id?}';

    protected $description = 'Merges room data from multiple suppliers based on semantic similarity and caches the result.';

    public function handle()
    {
        $this->info('Starting data merge process...');

        $giataId = $this->argument('giata_id');
        $cacheKey = 'supplier_merge_data'.($giataId ? "_{$giataId}" : '');
        $mergedDataArray = Cache::get($cacheKey);

        if ($mergedDataArray) {
            $this->info('Data found in cache. Using cached data.');
        } else {
            $this->info('Data not found in cache. Calling OpenAI API...');

            $supplierDataInput = $this->argument('supplierData');
            $supplierData = json_decode($supplierDataInput, true);
            if ($supplierData === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Failed to decode supplierData JSON: '.json_last_error_msg());

                return;
            }

            $supplierJson = json_encode($supplierData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $responseOpenAI = OpenAI::chat()->create([
                'model' => 'gpt-4.1-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a programming assistant. Your task is to semantically match names from multiple arrays based on a supplier key.',
                    ],
                    [
                        'role' => 'user',
                        'content' => <<<EOT
You have a JSON object where each key represents a supplier and the value is an array of hotel room data. Your task is to semantically match the listings based on the "name" field across all suppliers and create a merged JSON array. Each element in the output array should represent a single unified room and contain a "listings_to_merge" key with an array of the matched listings.
The output MUST be a valid JSON array. For the "merge_id" field, you must provide a unique string identifier. This ID should not be a simple number. It should be a generated, unique code, for example, a short, descriptive abbreviation of the room names being merged, to ensure it is unique across different hotels and room types.

Input JSON:
$supplierJson

Your JSON output must follow this structure:
[
    {
        "merge_id": "unique-id-for-the-merged-pair",
        "listings_to_merge": [
            {
                "code": "code_from_supplier",
                "name": "name_from_supplier",
                "supplier": "supplier_name"
            },
            {
                "code": "code_from_another_supplier",
                "name": "name_from_another_supplier",
                "supplier": "another_supplier_name"
            }
        ]
    }
]
EOT
                    ],
                ],
            ]);

            $mergedDataJson = $responseOpenAI->choices[0]->message->content;

            $mergedDataArray = json_decode($mergedDataJson, true);

            if ($mergedDataArray === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Failed to decode JSON from OpenAI response: '.json_last_error_msg());

                return;
            }

            $this->info('Storing data in cache...');
            Cache::put($cacheKey, $mergedDataArray, now()->addHours(24));

            logger()->debug('LoggerFlowHotel _ MergeSupplierData Command', ['cacheKey' => $cacheKey, 'output' => $mergedDataArray]);

        }

        $this->info('Final result:');
    }
}
