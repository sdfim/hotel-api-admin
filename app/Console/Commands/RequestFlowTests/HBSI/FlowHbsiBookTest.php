<?php

namespace App\Console\Commands\RequestFlowTests\HBSI;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class FlowHbsiBookTest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:hbsi-book-test {step?} {destination?} {supplier?} {type?}';

    protected PendingRequest $client;

    protected string $url;

    private ?string $destination;

    private ?string $supplier;

    private ?array $query;

    private ?string $type;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST', 'http://localhost:8000');
    }

    public function handle(): void
    {
        $step = $this->argument('step');
        $this->destination = $this->argument('destination');
        $this->supplier = $this->argument('supplier');
        $this->type = $this->argument('type');

        $this->destination = ! $this->destination ? '508' : $this->destination;
        $this->supplier = ! $this->supplier ? 'HBSI' : $this->supplier;
        $step = ! $step ? 1 : $step;
        $this->type = $this->type ?? 'test';

        foreach (range(1, $step) as $index) {
            $this->warn('STEP '.$index.' of '.$step);
            $this->flow();
        }
    }

    private function searchAndAddItem(int $s = 1): array
    {
        $retryCount = 0;
        $bookingItem = null;
        $this->warn('SEARCH '.$s);
        while ($retryCount < 7 && $bookingItem === null) {
            $responseData = $this->makeSearchRequest($s);
            if (! isset($responseData['data']['query']['occupancy'])) {
                $retryCount++;

                continue;
            }

            $this->info('query search_'.$s.' = '.json_encode($responseData['data']['query']));

            $this->query['search_'.$s] = $responseData['data']['query']['occupancy'];
            $searchId = $responseData['data']['search_id'];
            $bookingItem = $this->getBookingItem($responseData);

            $retryCount++;
        }
        $this->info('search_id = '.$searchId ?? 'null');
        $this->info('booking_item = '.$bookingItem['booking_item'] ?? 'null');

        return $bookingItem;
    }

    public function flow(): void
    {
        $this->query = [];
        $bookingItem = $this->searchAndAddItem(1);
        $bookingId = $this->addBookingItem($bookingItem['booking_item']);
        $bookingItems['search_1'] = $bookingItem['booking_item'];
        $bookingRateOrdinals['search_1'] = $bookingItem['rate_ordinal'];

        $bookingItem = $this->searchAndAddItem(2);
        $bookingId = $this->addBookingItem($bookingItem['booking_item'], $bookingId);
        $bookingItems['search_2'] = $bookingItem['booking_item'];
        $bookingRateOrdinals['search_2'] = $bookingItem['rate_ordinal'];

        $this->warn('addPassengers group for SEARCH 1, SEARCH 2');
        $this->addPassengers($bookingId, $bookingItems, $this->query);

        $this->query = [];
        $bookingItem = $this->searchAndAddItem(3);
        $bookingId = $this->addBookingItem($bookingItem['booking_item'], $bookingId);
        $bookingItems2['search_3'] = $bookingItem['booking_item'];
        $bookingRateOrdinals2['search_3'] = $bookingItem['rate_ordinal'];

        $this->warn('addPassengers group for search_3');
        $this->addPassengers($bookingId, $bookingItems2, $this->query);

        $this->warn('REMOVE ITEM');
        $this->removeBookingItem($bookingId, $bookingItem['booking_item']);

        $this->warn('RETRIEVE ITEMS');
        $this->retrieveItems($bookingId);

        //        $this->warn('BOOK '.$bookingId);
        //        $this->book($bookingId, $bookingItems);
    }

    private function getBookingItem(array $responseData): ?array
    {
        $flattened = Arr::dot($responseData);

        $countRooms = count($responseData['data']['query']['occupancy']);

        $bookingItems = [];
        if ($countRooms === 1) {
            //            foreach ($flattened as $key => $value) {
            //                if (str_contains($key, 'booking_item')) {
            //                    $bookingItems[$key] = $value;
            //                }
            //            }
            $hotels = $responseData['data']['results'];

            foreach ($hotels as $hotel) {
                $ro = 1;
                foreach ($hotel['room_groups'] as $room_groups) {
                    foreach ($room_groups['rooms'] as $room) {
                        if (! $room['non_refundable']) {
                            $booking_item = $room['booking_item'];
                            $rate_ordinal = $ro;
                            $ro++;
                            break 3;
                        }
                    }
                }
            }
        } else {
            foreach ($flattened as $key => $value) {
                if (str_contains($key, 'room_combinations')) {
                    $bookingItems[$key] = explode('.', $key)[4];
                }
            }
        }

        if ($countRooms !== 1) {
            if (empty($bookingItems)) {
                return null;
            }
            $randomKey = array_rand($bookingItems);
            $booking_item = $bookingItems[$randomKey];
            $rate_ordinal = $flattened[str_replace('room_combinations', 'rate_ordinal', $randomKey)] ?? null;
        }

        return [
            'booking_item' => $booking_item,
            'rate_ordinal' => $rate_ordinal,
        ];
    }

    private function makeSearchRequest(int $count = 1): array
    {
        $faker = Faker::create();
        if ($count > 2) {
            $count = 2;
        }
        if ($this->type != 'test') {
            $checkin = Carbon::now()->addDays(240)->toDateString();
            $checkout = Carbon::now()->addDays(241 + rand(2, 5))->toDateString();
        } else {
            $checkin = Carbon::now()->addDays(30)->toDateString();
            $checkout = Carbon::now()->addDays(30 + rand(2, 5))->toDateString();
        }

        $occupancy = [];
        foreach (range(1, $count) as $index) {
            $room['adults'] = rand(1, 2);
            $occupancy[] = $room;
        }

        $requestData = [
            'type' => 'hotel',
            'destination' => $this->destination,
            'supplier' => $this->supplier,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
            'results_per_page' => 100,
        ];

        $response = $this->client->post($this->url.'/api/pricing/search', $requestData);

        return $response->json();
    }

    private function addBookingItem(string $bookingItem, ?string $bookingId = null): string
    {
        $requestData = ['booking_item' => $bookingItem];
        if ($bookingId !== null) {
            $requestData['booking_id'] = $bookingId;
        }

        $response = $this->client->post($this->url.'/api/booking/add-item', $requestData);

        $this->info('addBookingItem: '.json_encode($response->json()));
        $bookingId = $response->json()['data']['booking_id'];
        $this->info('booking_id = '.$bookingId);

        return $bookingId;
    }

    private function addPassengers(string $bookingId, array $bookingItems, array $occupancy): void
    {
        $faker = Faker::create();

        $requestData = ['passengers' => []];

        foreach ($bookingItems as $keySearch => $bookingItem) {
            $roomCounter = 1;
            foreach ($occupancy[$keySearch] as $occupant) {
                for ($i = 0; $i < $occupant['adults']; $i++) {
                    $passenger = [
                        'title' => 'mr',
                        'given_name' => $faker->firstName(),
                        'family_name' => $faker->lastName(),
                        'date_of_birth' => $faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                        'booking_items' => [
                            [
                                'booking_item' => $bookingItem,
                                'room' => $roomCounter,
                            ],
                        ],
                    ];

                    $requestData['passengers'][] = $passenger;
                }

                if (isset($occupant['children_ages']) && count($occupant['children_ages']) > 0) {
                    foreach ($occupant['children_ages'] as $childAge) {
                        $passenger = [
                            'title' => 'ms',
                            'given_name' => 'Child',
                            'family_name' => 'Donald',
                            'date_of_birth' => date('Y-m-d', strtotime("-$childAge years")),
                            'booking_items' => [
                                [
                                    'booking_item' => $bookingItem,
                                    'room' => $roomCounter,
                                ],
                            ],
                        ];

                        $requestData['passengers'][] = $passenger;
                    }
                }
                $roomCounter++;
            }
        }

        $requestData['booking_id'] = $bookingId;

        $response = $this->client->post($this->url.'/api/booking/add-passengers', $requestData);

        $this->info('addPassengers: '.json_encode($response->json()));
    }

    private function removeBookingItem(string $bookingId, string $bookingItem): void
    {
        $requestData = [
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
        ];

        $response = $this->client->delete($this->url.'/api/booking/remove-item', $requestData);
        $this->info('removeBookingItem: '.json_encode($response->json()));
    }

    private function retrieveItems(string $bookingId): void
    {
        $requestData = [
            'booking_id' => $bookingId,
        ];

        $response = $this->client->get($this->url.'/api/booking/retrieve-items', $requestData);
        $this->info('retrieveItems: '.json_encode($response->json()));
    }

    private function book(string $bookingId, array $bookingItems): void
    {
        $faker = Faker::create();

        $requestData = [
            'booking_id' => $bookingId,
            'amount_pay' => 'Deposit',
            'booking_contact' => [
                'first_name' => 'Andri test',
                'last_name' => 'TEST',
                'email' => 'test@gmail.com',
                'phone' => [
                    'country_code' => '1',
                    'area_code' => '487',
                    'number' => '5550077',
                ],
                'address' => [
                    'line_1' => '5047 Kessler Glens', //$faker->streetAddress(),
                    'city' => 'Ortizville', //$faker->city(),
                    'state_province_code' => 'VT', //$faker->stateAbbr(),
                    'postal_code' => 'mt', //$faker->lexify(str_repeat('?', rand(1, 7))), //$faker->postcode(),
                    'country_code' => 'US', //$faker->countryCode(),
                ],
            ],
            'special_requests' => [
                [
                    'booking_item' => $bookingItems['search_1'],
                    'room' => 1,
                    'special_request' => 'TerraMare Test Booking, please disregard.',
                ],
            ],
        ];

        if ($this->supplier === 'HBSI') {
            foreach ($bookingItems as $item) {
                $cards[] = [
                    'credit_card' => [
                        'cvv' => '123',
                        'number' => 4001919257537193,
                        'card_type' => 'VISA',
                        'name_card' => 'Visa',
                        'expiry_date' => '09/2026',
                        'billing_address' => null,
                    ],
                    'booking_item' => $item,
                ];
            }
            $requestData['credit_cards'] = $cards;
        }

        $response = $this->client->post($this->url.'/api/booking/book', $requestData);
        $this->info('book: '.json_encode($response->json()));
    }
}
