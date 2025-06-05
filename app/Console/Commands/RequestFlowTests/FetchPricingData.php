<?php

namespace App\Console\Commands\RequestFlowTests;

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
        $checkin = now()->addDays(60)->format('Y-m-d');
        $checkout = now()->addDays(61)->format('Y-m-d');

        $giata_id = $this->argument('giata_id') ?? 21569211;
        $giata_id = is_numeric($giata_id) ? (int) $giata_id : $giata_id;

        $requestData = [
            'type' => 'hotel',
            'rating' => 2,
            'giata_ids' => [$giata_id],
            'supplier' => 'HBSI',
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => [['adults' => 2]],
        ];

        logger()->info('Fetch pricing data from the API', $requestData);

        $client = Http::withToken(env('TEST_TOKEN'));
        $baseUri = env('BASE_URI_FLOW_HBSI_BOOK_TEST');

        $response = $client->post($baseUri.'/api/pricing/search', $requestData);

        if (! $response->successful()) {
            $this->error('Failed to fetch pricing data: '.$response->status());

            return 1;
        }

        $roomsFound = 0;

        $responseData = $response->json();

        $roomGroups = Arr::get($responseData, 'data.results.0.room_groups', []);

        $dataPricingSupplier = [];

        foreach ($roomGroups as $roomGroup) {
            if (isset($roomGroup['rooms'])) {
                foreach ($roomGroup['rooms'] as $room) {
                    if (isset($room['supplier_room_name']) && isset($room['room_type'])) {
                        $dataPricingSupplier[] = [
                            $room['supplier_room_name'] => 'URC-'.$room['room_type'],
                        ];
                    }
                    try {
                        MappingRoom::updateOrCreate(
                            [
                                'giata_id' => $requestData['giata_ids'][0] ?? null,
                                'supplier' => $requestData['supplier'] ?? '',
                                'supplier_room_code' => $room['room_type'] ?? '',
                                'unified_room_code' => 'URC-'.$room['room_type'] ?? '',
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

                    $roomsFound++;
                    logger()->info('Room saved: ', [
                        'giata_id' => $giata_id,
                        'supplier' => $requestData['supplier'] ?? '',
                        'supplier_room_code' => $room['room_type'] ?? '',
                        'unified_room_code' => 'URC-'.$room['room_type'] ?? '',
                    ]);
                }
            }
        }

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

        $responseOpenAI = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Ты помощник-программист. Твоя задача — семантически сопоставить названия из двух массивов.',
                ],
                [
                    'role' => 'user',
                    'content' => <<<EOT
У тебя есть два массива.

Первый:
[
    {$this->formatArrayForPrompt($dataPricingSupplier)}
]

Второй:
[
    {$this->formatArrayForPrompt($expediaRoomsData)}
]

Найди семантические совпадения между названиями из второго массива и названиями из первого.

Верни массив, где:
- ключи — названия из второго массива
- значения — соответствующие коды из первого массива

Включай только те, для которых есть очевидное совпадение. Вернуть нужно только массив, без дополнительного текста.
EOT
                ],
            ],
        ]);

        $matches = $responseOpenAI['choices'][0]['message']['content'];
        $parsed = json_decode($matches, true);
        $flattened = [];
        foreach ($parsed as $item) {
            $flattened += $item;
        }

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
