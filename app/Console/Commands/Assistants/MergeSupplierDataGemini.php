<?php

namespace App\Console\Commands\Assistants;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MergeSupplierDataGemini extends Command
{
    protected $signature = 'merge:suppliers:gemini-provider {supplierData} {giata_id?}';

    protected $description = 'Merges room data from multiple suppliers using Gemini 2.0 based on semantic similarity.';

    public function handle()
    {
        $this->info('Starting data merge process using Gemini 2.0...');

        $giataId = $this->argument('giata_id');
        $cacheKey = 'supplier_merge_data'.($giataId ? "_{$giataId}" : '');
        $mergedDataArray = Cache::get($cacheKey);

        if ($mergedDataArray) {
            $this->info('Data found in cache. Using cached data.');
        } else {
            $this->info('Data not found in cache. Calling Gemini 2.0 API...');

            $supplierDataInput = $this->argument('supplierData');
            $supplierData = json_decode($supplierDataInput, true);
            if ($supplierData === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Failed to decode supplierData JSON: '.json_last_error_msg());

                return;
            }

            $supplierJson = json_encode($supplierData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $systemPrompt = 'You are a programming assistant. Your task is to semantically match names from multiple arrays based on a supplier key.';
            $userPrompt = <<<EOT
You have a JSON object where each key represents a supplier and the value is an array of hotel room data. Your task is to semantically match the listings based on the "name" field across all suppliers and create a merged JSON array. Each element in the output array should represent a single unified room and contain a "listings_to_merge" key with an array of the matched listings.

It is crucial that you accurately identify rooms that are distinct based on their attributes, such as a "balcony." For example, a "Deluxe Twin Room, Balcony" and a "Deluxe Twin Room" are separate room types and should not be merged.

The output MUST be a valid JSON array. For the "merge_id" field, you must provide a unique string identifier. This ID should not be a simple number. It should be a generated, unique code, for example, a short, descriptive abbreviation of the room names being merged, to ensure it is unique across different hotels and room types.

**Example Input:**
{
    "Expedia": [
        {"code": "320819385", "name": "Deluxe Twin Room, Garden View"},
        {"code": "321081001", "name": "Deluxe Twin Room, Balcony, Garden View"}
    ],
    "IcePortal": [
        {"code": "DGQ", "name": "Deluxe Garden View Twin Room,Twin Room,Deluxe,Garden View"},
        {"code": "DGBQ", "name": "Deluxe Balcony Garden View Twin Room,Twin Room,Deluxe,Garden View"}
    ]
}

**Example Output:**
[
    {
        "merge_id": "deluxe-twin-garden-view",
        "listings_to_merge": [
            {
                "code": "320819385",
                "name": "Deluxe Twin Room, Garden View",
                "supplier": "Expedia"
            },
            {
                "code": "DGQ",
                "name": "Deluxe Garden View Twin Room,Twin Room,Deluxe,Garden View",
                "supplier": "IcePortal"
            }
        ]
    },
    {
        "merge_id": "deluxe-twin-balcony-garden-view",
        "listings_to_merge": [
            {
                "code": "321081001",
                "name": "Deluxe Twin Room, Balcony, Garden View",
                "supplier": "Expedia"
            },
            {
                "code": "DGBQ",
                "name": "Deluxe Balcony Garden View Twin Room,Twin Room,Deluxe,Garden View",
                "supplier": "IcePortal"
            }
        ]
    }
]

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
EOT;

            try {
                $model = config('services.gemini.model');
                $apiKey = config('services.gemini.api_key');

                $responseGemini = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt],
                                ['text' => $userPrompt],
                            ],
                        ],
                    ],
                ]);

                logger()->debug('LoggerFlowHotel _ MergeSupplierDataGemini Command 1 ', ['cacheKey' => $cacheKey, 'responseGemini' => $responseGemini->json()]);

                // Extract the JSON string from the response
                $geminiResponseData = $responseGemini->json();

                // Check if the expected data path exists
                if (isset($geminiResponseData['candidates'][0]['content']['parts'][0]['text'])) {
                    $rawText = $geminiResponseData['candidates'][0]['content']['parts'][0]['text'];

                    // Remove the markdown code block markers
                    $mergedDataJson = str_replace(['```json', '```'], '', $rawText);
                    $mergedDataJson = trim($mergedDataJson); // Trim any whitespace

                    $mergedDataArray = json_decode($mergedDataJson, true);

                    if ($mergedDataArray === null && json_last_error() !== JSON_ERROR_NONE) {
                        $this->error('Failed to decode JSON from Gemini 2.0 response: '.json_last_error_msg());

                        return;
                    }

                    $this->info('Storing data in cache...');
                    Cache::put($cacheKey, $mergedDataArray, now()->addHours(24));

                    logger()->debug('LoggerFlowHotel _ MergeSupplierDataGemini Command 2 ', ['cacheKey' => $cacheKey, 'output' => $mergedDataArray]);
                } else {
                    $this->error('Invalid response format from Gemini 2.0 API.');

                    return;
                }

            } catch (\Exception $e) {
                $this->error('Error calling Gemini 2.0 API: '.$e->getMessage());
                logger()->error('LoggerFlowHotel _ MergeSupplierDataGemini Command', [
                    'error' => $e->getMessage(),
                    'supplierData' => $supplierDataInput,
                ]);

                return;
            }
        }

        $this->info('Final result:');
        $this->line(json_encode($mergedDataArray, JSON_PRETTY_PRINT));
    }
}
