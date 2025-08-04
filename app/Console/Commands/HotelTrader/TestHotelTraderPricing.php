<?php

namespace App\Console\Commands\HotelTrader;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\HotelTraderSupplier\HotelTraderClient;
use Symfony\Component\Console\Command\Command as CommandAlias;

class TestHotelTraderPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:hotel-trader-pricing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the HotelTraderClient for getPropertiesByIds query.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(HotelTraderClient $hotelTraderClient) // Внедряем клиент
    {
        $this->info('Starting HotelTrader pricing test...');

        // Переменные для запроса getPropertiesByIds
        $searchCriteriaByIdsInput = [
            'SearchCriteriaByIds' => [
                'propertyIds' => [2262291],
                'occupancies' => [
                    [
                        'checkInDate' => '2025-08-07',
                        'checkOutDate' => '2025-08-12',
                        'guestAges' => '33,33',
                    ],
                    [
                        'checkInDate' => '2025-08-07',
                        'checkOutDate' => '2025-08-12',
                        'guestAges' => '33,33,5',
                    ],
                ],
            ],
        ];

        try {
            $response = $hotelTraderClient->sendSearchQuery($searchCriteriaByIdsInput);

            if ($response) {
                $this->info('API Response:');
                $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                if (isset($response['data']['getPropertiesByIds']['properties'])) {
                    $properties = $response['data']['getPropertiesByIds']['properties'];
                    $this->info("\nFound ".count($properties).' properties:');
                    foreach ($properties as $property) {
                        $this->line('  Property ID: '.$property['propertyId'].', Name: '.$property['propertyName']);
                        if (isset($property['rooms'])) {
                            $this->line('    Rooms available: '.count($property['rooms']));
                            foreach ($property['rooms'] as $room) {
                                $this->line('      Room: '.$room['roomName'].' (Code: '.$room['roomCode'].')');
                                if (isset($room['rateInfo']['grossPrice'])) {
                                    $this->line('        Gross Price: '.$room['rateInfo']['grossPrice'].' '.$room['rateInfo']['currency']);
                                } else {
                                    $this->line('        Price not available for this room.');
                                }
                            }
                        }
                    }
                } else {
                    $this->warn('No properties found in the response data.');
                }
            } else {
                $this->error('Failed to get response from HotelTrader API.');
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: '.$e->getMessage());
            Log::error('TestHotelTraderPricing Command Error: '.$e->getMessage());
        }

        $this->info('HotelTrader pricing test finished.');

        return CommandAlias::SUCCESS;
    }
}
