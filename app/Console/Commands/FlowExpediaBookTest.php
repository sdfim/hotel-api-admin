<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class FlowExpediaBookTest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'expedia-book-test {step} {destination} {supplier}';


    protected $description = 'Command description';

    /** @var PendingRequest */
    protected PendingRequest $client;

    protected string $url;

    protected string $destination;

    protected string $supplier;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST', 'http://localhost:8000');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $step = $this->argument('step');
        $this->destination = $this->argument('destination');
        $this->supplier = $this->argument('supplier');

        foreach (range(1, $step) as $index) {
            $this->warn('STEP ' . $index . ' of ' . $step);
            $this->strategy1();
        }
    }

    /**
     * @param array $responseData
     * @return string
     */
    private function getBookingItemOld(array $responseData): string
    {
        $flattened = Arr::dot($responseData);

        $bookingItems = [];
        foreach ($flattened as $key => $value) {
            if (str_contains($key, 'booking_item')) {
                $bookingItems[] = $value;
            }
        }
        return $bookingItems[array_rand($bookingItems)];
    }

    /**
     * @param array $responseData
     * @return string
     */
    private function getBookingItem(array $responseData): string
    {
        $filteredItems = [];
        foreach ($responseData['data']['results'] as $room_groups) {
            foreach ($room_groups['room_groups'] as $room_group) {
                foreach ($room_group['rooms'] as $room) {
                    foreach ($room['cancellation_policies'] as $policy) {
                        if (isset($policy['nights']) && $policy['nights'] === '1') {
                            $filteredItems[] = $room['booking_item'];
                        }
                    }
                }
            }
        }

//        dd($filteredItems[array_rand($filteredItems)], $filteredItems);

        return $filteredItems[array_rand($filteredItems)];
    }

    /**
     * @return void
     */
    public function strategy1(): void
    {
        $this->warn('SEARCH 1');
        $responseData1 = $this->makeSearchRequest(2);
        $query['search_1'] = $responseData1['data']['query']['occupancy'];
        $searchId = $responseData1['data']['search_id'];
        $bookingItem = $this->getBookingItem($responseData1);
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        $bookingId = $this->addBookingItem($bookingItem);
        $bookingItems['search_1'] = $bookingItem;

        $this->warn('SEARCH 2');
        $responseData2 = $this->makeSearchRequest();
        $query['search_2'] = $responseData2['data']['query']['occupancy'];
        $searchId = $responseData2['data']['search_id'];
        $bookingItem = $this->getBookingItem($responseData2);
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        $bookingId = $this->addBookingItem($bookingItem, $bookingId);
        $bookingItems['search_2'] = $bookingItem;

        $this->warn('addPassengers group for SEARCH 1, SEARCH 2');
        $this->addPassengers($bookingId, $bookingItems, $query);

        if ($this->argument('step') !== '1') {
            $this->warn('SEARCH 3');
            $responseData = $this->makeSearchRequest(2);
            $query2['search_3'] = $responseData['data']['query']['occupancy'];
            $searchId = $responseData['data']['search_id'];
            $bookingItem = $this->getBookingItem($responseData);
            $this->info('search_id = ' . $searchId);
            $this->info('booking_item = ' . $bookingItem);

            $bookingId = $this->addBookingItem($bookingItem, $bookingId);
            $bookingItems2['search_3'] = $bookingItem;
            $this->addPassengers($bookingId, $bookingItems2, $query2);

            sleep(3);
            $this->warn('REMOVE ITEM');
            $this->removeBookingItem($bookingId, $bookingItem);
        }

        $this->warn('RETRIEVE ITEMS');
        $this->retrieveItems($bookingId);

        $this->warn('BOOK ' . $bookingId);
        $this->book($bookingId);
    }

    /**
     * @param int $count
     * @return array
     */
    private function makeSearchRequest(int $count = 1): array
    {
        $faker = Faker::create();
//        $checkin = Carbon::now()->addDays(1)->toDateString();
//        $checkout = Carbon::now()->addDays(2 + rand(2, 5))->toDateString();
        $checkin = '2024-12-15';
        $checkout = '2024-12-17';

        $occupancy = [];
        foreach (range(1, $count) as $index) {

            $room['adults'] = $faker->numberBetween(1, 3);

            if ($count % 2 != 0) $children = rand(0, 2);
            else $children = 0;
            $children_ages = [];
            if ($children > 0) {
                foreach (range(1, $children) as $index) {
                    $children_ages[] = rand(1, 17);
                }
                $room['children'] = $children;
                $room['children_ages'] = $children_ages;
            }

            $occupancy[] = $room;
        }

        $requestData = [
            'type' => 'hotel',
            'currency' => 'USD',
            'destination' => $this->destination,
            'supplier' => $this->supplier,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
            'rating' => $faker->numberBetween(4, 5),
        ];

        $response = $this->client->post($this->url . '/api/pricing/search', $requestData);
        return $response->json();
    }

    /**
     * @param string $bookingItem
     * @param string|null $bookingId
     * @return string
     */
    private function addBookingItem(string $bookingItem, ?string $bookingId = null): string
    {
        $requestData = [
            'booking_item' => $bookingItem,
        ];

        if ($bookingId !== null) {
            $requestData['booking_id'] = $bookingId;
        }

        $response = $this->client->post($this->url . '/api/booking/add-item', $requestData);
        $bookingId = $response->json()['data']['booking_id'];
        $this->info('booking_id = ' . $bookingId);

        return $bookingId;
    }

    /**
     * @param string $bookingId
     * @param array $bookingItems
     * @param array $occupancy
     * @return void
     */
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
                        'given_name' => $faker->firstName,
                        'family_name' => $faker->lastName,
                        'date_of_birth' => $faker->date('Y-m-d', strtotime('-' . rand(20, 60) . ' years')),
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

        $response = $this->client->post($this->url . '/api/booking/add-passengers', $requestData);

        $this->info('addPassengers: ' . json_encode($response->json()));
    }

    /**
     * @param string $bookingId
     * @param string $bookingItem
     * @return void
     */
    private function removeBookingItem(string $bookingId, string $bookingItem): void
    {
        $requestData = [
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
        ];

        $response = $this->client->delete($this->url . '/api/booking/remove-item', $requestData);
        $this->info('removeBookingItem: ' . json_encode($response->json()));
    }

    /**
     * @param string $bookingId
     * @return void
     */
    private function retrieveItems(string $bookingId): void
    {
        $requestData = [
            'booking_id' => $bookingId,
        ];

        $response = $this->client->get($this->url . '/api/booking/retrieve-items', $requestData);
        $this->info('retrieveItems: ' . json_encode($response->json()));
    }

    /**
     * @param string $bookingId
     * @return void
     */
    private function book(string $bookingId): void
    {
        $faker = Faker::create();

        $requestData = [
            'booking_id' => $bookingId,
            'amount_pay' => 'Deposit',
            'booking_contact' => [
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => 'test@gmail.com', //$faker->safeEmail,
                'phone' => [
                    'country_code' => '1',
                    'area_code' => '487',
                    'number' => '5550077',
                ],
                'address' => [
                    'line_1' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state_province_code' => $faker->stateAbbr,
                    'postal_code' => $faker->lexify(str_repeat('?', rand(1, 7))),
                    'country_code' => $faker->countryCode,
                ],
            ],
        ];

        $response = $this->client->post($this->url . '/api/booking/book', $requestData);
        $this->info('book: ' . json_encode($response->json()));
    }
}
