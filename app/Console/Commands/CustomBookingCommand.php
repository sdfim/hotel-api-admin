<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;


class CustomBookingCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'custom-booking-command {step}';
    /**
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var PendingRequest
     */
    protected PendingRequest $client;
    // protected const TOKEN = 'bE38wDtILir6aJWeFHA2EnHZaQQcwdFjn7PKFz3A482bcae2';
    // protected const BASE_URI = 'https://ddwlx1ki3fks2.cloudfront.net';

    /**
     *
     */
    protected const TOKEN = 'hbm7hrirpLznIX9tpC0mQ0BjYD9PXYArGIDvwdPs5ed1d774';
    /**
     *
     */
    protected const BASE_URI = 'http://localhost:8008';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(self::TOKEN);
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $step = $this->argument('step');

        foreach (range(1, $step) as $index) {
            $this->warn('STEP ' . $index . ' of ' . $step);
            $this->strategy1();
        }
    }

    /**
     * @param array $responseData
     * @return string
     */
    private function getBookingItem(array $responseData): string
    {
        $flattened = Arr::dot($responseData);

        $bookingItems = [];
        $i = 0;
        foreach ($flattened as $key => $value) {
            if (str_contains($key, 'booking_item')) {
                $bookingItems[$i] = $value;
                $i++;
            }
        }
        $i > 0 ? $i-- : $i;
        return $bookingItems[rand(0, $i)];
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
        $checkin = Carbon::now()->addDays()->toDateString();
        $checkout = Carbon::now()->addDays(1 + rand(2, 5))->toDateString();

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
            'currency' => $faker->randomElement(['USD', 'EUR', 'GBP', 'CAD', 'JPY']),
            'destination' => $faker->randomElement([961, 302, 93, 960, 1102]),
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
            'rating' => $faker->numberBetween(3, 5),
        ];

        $response = $this->client->post(self::BASE_URI . '/api/pricing/search', $requestData);
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

        $response = $this->client->post(self::BASE_URI . '/api/booking/add-item', $requestData);
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

        $response = $this->client->post(self::BASE_URI . '/api/booking/add-passengers', $requestData);

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

        $response = $this->client->delete(self::BASE_URI . '/api/booking/remove-item', $requestData);
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

        $response = $this->client->get(self::BASE_URI . '/api/booking/retrieve-items', $requestData);
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
                'email' => $faker->email,
                'phone' => [
                    'country_code' => '1',
                    'area_code' => '487',
                    'number' => '5550077',
                ],
                'address' => [
                    'line_1' => $faker->streetAddress,
                    'city' => $faker->city,
                    'state_province_code' => $faker->stateAbbr,
                    'postal_code' => $faker->lexify(str_repeat('?', rand(1, 7))), //$faker->postcode,
                    'country_code' => $faker->countryCode,
                ],
            ],
        ];

        $response = $this->client->post(self::BASE_URI . '/api/booking/book', $requestData);
        $this->info('book: ' . json_encode($response->json()));
    }
}
