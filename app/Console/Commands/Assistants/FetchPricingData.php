<?php

namespace App\Console\Commands\Assistants;

use App\Models\Channel;
use App\Models\ExpediaContentSlave;
use App\Models\MappingRoom;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use OpenAI\Laravel\Facades\OpenAI;

class FetchPricingData extends Command
{
    protected $signature = 'api:fetch-pricing {giata_id?}';

    protected $description = 'Fetch pricing data from the API';

    public function handle()
    {
        $checkin = now()->addDays(70)->format('Y-m-d');
        $checkout = now()->addDays(71)->format('Y-m-d');

        $giata_id = $this->argument('giata_id') ?? 21569211;
        $giata_id = is_numeric($giata_id) ? (int) $giata_id : $giata_id;

        // Expedia
        $expediaRooms = ExpediaContentSlave::whereHas('mapperGiataExpedia', function ($query) use ($giata_id) {
            $query->where('giata_id', $giata_id);
        })
            ->get()
            ->pluck('rooms')
            ->toArray();

        $expediaRoomsData = [];
        foreach ($expediaRooms[0] as $index => $room) {
            $expediaRoomsData[] = [
                $room['name'] => $index,
            ];
        }

        if (empty($expediaRoomsData)) {
            $this->error('No content supplier data found. Terminating execution.');

            return 1; // Exit the command with an error code
        }

        $requestData = [
            'type' => 'hotel',
            'rating' => 2,
            'giata_ids' => [$giata_id],
            'supplier' => 'HBSI',
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => [['adults' => 2], ['adults' => 1]],
        ];

        logger()->info('Fetch pricing data from the API', $requestData);

        $token = Channel::first()->access_token;
        if (! $token) {
            $this->error('No access token found for the channel. Please check your configuration.');

            return 1; // Exit the command with an error code
        }
        $client = Http::withToken($token);
        $baseUri = env('APP_URL');

        $response = $client->post($baseUri.'/api/pricing/search', $requestData);

        if (! $response->successful()) {
            $this->error('Failed to fetch pricing data: '.$response->status());

            return 1;
        }

        $responseData = $response->json();

        $roomGroups = Arr::get($responseData, 'data.results.0.room_groups', []);

        $dataPricingSupplier = [];

        foreach ($roomGroups as $roomGroup) {
            if (isset($roomGroup['rooms'])) {
                foreach ($roomGroup['rooms'] as $room) {
                    if (isset($room['supplier_room_name']) && isset($room['room_type'])) {
                        $dataPricingSupplier[] = [
                            $room['supplier_room_name'] => 'IBS-'.$giata_id.'-'.$room['room_type'],
                        ];
                    }
                    try {
                        MappingRoom::updateOrCreate(
                            [
                                'giata_id' => $giata_id,
                                'supplier' => $requestData['supplier'] ?? '',
                                'supplier_room_code' => $room['room_type'] ?? '',
                                'unified_room_code' => 'IBS-'.$giata_id.'-'.$room['room_type'] ?? '',
                            ],
                            [
                                'supplier_room_name' => $room['supplier_room_name'] ?? '',
                                'match_percentage' => 100,
                            ]
                        );
                    } catch (\Exception $e) {
                        $this->error('Error processing room type: '.$e->getMessage());
                        logger()->error('Error processing room type: ', [
                            'giata_id' => $giata_id,
                            'supplier' => $requestData['supplier'] ?? '',
                            'supplier_room_code' => $room['room_type'] ?? '',
                            'error' => $e->getMessage(),
                        ]);

                        continue;
                    }

                    logger()->info('Room saved: ', [
                        'giata_id' => $giata_id,
                        'supplier' => $requestData['supplier'] ?? '',
                        'supplier_room_code' => $room['room_type'] ?? '',
                        'unified_room_code' => 'IBS-'.$giata_id.'-'.$room['room_type'] ?? '',
                    ]);
                }
            }
        }

        $dataPricingSupplier = array_values($dataPricingSupplier);
        $roomsFound = count($dataPricingSupplier);

        $responseOpenAI = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a programming assistant. Your task is to semantically match names from two arrays.',
                ],
                [
                    'role' => 'user',
                    'content' => <<<EOT
You have two arrays.

First:
[
    {$this->formatArrayForPrompt($dataPricingSupplier)}
]

Second:
[
    {$this->formatArrayForPrompt($expediaRoomsData)}
]

Find semantic matches between names from the second array and names from the first array.
Names may have slight differences in wording but refer to the same concept. Consider the following when matching:
Words like “Suite” can be optional and may be missing in one of the arrays.
Word order may differ (e.g., "Master Ocean Front" vs "Ocean Front Master").
Matching should be based on meaning, not exact wording.
Synonyms or near-synonyms (e.g., "View" vs "Vista", if applicable) can be treated as equal.
Ignore extra descriptive words that do not change the core meaning.
Examples:
"Master Suite Ocean Front" matches "Master Ocean Front"
"Master Suite Ocean View" matches "Master Ocean View"

Return an array where:
- keys are names from the second array
- values are corresponding codes from the first array

Include only those with obvious matches. Return only the array as a valid JSON object, not an array of objects.
The format should be a single JSON object like this:
{
  "Room name 1": "CODE1",
  "Room name 2": "CODE2"
}
Do not return an array of objects like [{"key": "value"}, {"key2": "value2"}].
EOT
                ],
            ],
        ]);

        $matches = $responseOpenAI['choices'][0]['message']['content'];

        // Extract JSON content if wrapped in markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/m', $matches, $extractedJson)) {
            $matches = $extractedJson[1];
        }

        $parsed = json_decode($matches, true) ?? [];
        $flattened = [];

        logger()->info('OpenAI response: ', [
            'matches' => $matches,
            'parsed' => $parsed,
            'response' => $responseOpenAI,
            'giata_id' => $giata_id,
            'expediaRoomsData' => $expediaRoomsData,
            'dataPricingSupplier' => $dataPricingSupplier,
        ]);

        // Handle different response formats
        if (is_array($parsed)) {
            // If we have a non-empty array and the first element is not an array,
            // it's already a flattened associative array with key-value pairs
            $firstValue = reset($parsed);
            $firstKey = key($parsed);

            if (! empty($parsed) && is_string($firstKey) && is_string($firstValue)) {
                // Response is already a flat associative array, use it directly
                $flattened = $parsed;
            } else {
                // Handle array of objects format [{"key": "value"}, {"key2": "value2"}]
                foreach ($parsed as $item) {
                    if (is_array($item)) {
                        $flattened += $item;
                    }
                }
            }
        } else {
            $this->error('Invalid response format from OpenAI API');
            logger()->error('Invalid response format from OpenAI API', [
                'matches' => $matches,
                'parsed' => $parsed,
            ]);
        }

        $countSave = 0;
        foreach ($expediaRoomsData as $room) {
            if (isset($flattened[key($room)])) {
                $unifiedRoomCode = $flattened[key($room)];
                MappingRoom::updateOrCreate(
                    [
                        'giata_id' => $giata_id,
                        'supplier' => 'Expedia',
                        'supplier_room_code' => $room[key($room)],
                        'unified_room_code' => $unifiedRoomCode,
                    ],
                    [
                        'supplier_room_name' => key($room),
                        'match_percentage' => 90,
                    ]
                );
                $countSave++;
            }
        }

        $this->info('API Response: '.$response->status().' count of rooms found: '.$roomsFound);

        return 0;
    }

    protected function formatArrayForPrompt(array $array): string
    {
        return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
