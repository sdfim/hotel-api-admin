<?php

namespace App\Console\Commands;

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
     *
     * @var string
     */
    protected $signature = 'hbsi-book-test {step} {destination} {supplier}';

    protected PendingRequest $client;
    protected string $url;
    private string $destination;
    private string $supplier;
    private array $query;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST');
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $step = $this->argument('step');
        $faker = Faker::create();
        $this->destination = $this->argument('destination') ?? $faker->randomElement([961, 302, 93, 960, 1102]);
        $this->supplier = $this->argument('supplier') ?? $faker->randomElement(['Expedia', 'HBSI']);

        foreach (range(1, $step) as $index) {
            $this->warn('STEP ' . $index . ' of ' . $step);
            $this->flow();
        }
    }

    private function searchAndAddItem(int $s = 1): array
    {
        $retryCount = 0;
        $bookingItem = null;
        $this->warn('SEARCH ' . $s);
        while ($retryCount < 7 && $bookingItem === null) {
            $responseData = $this->makeSearchRequest($s);
            if (!isset($responseData['data']['query']['occupancy'])) continue;
            $this->query['search_' . $s] = $responseData['data']['query']['occupancy'];
            $searchId = $responseData['data']['search_id'];
            $bookingItem = $this->getBookingItem($responseData);

            sleep(1);
            $retryCount++;
        }
        $this->info('search_id = ' . $searchId ?? 'null');
        $this->info('booking_item = ' . $bookingItem['booking_item'] ?? 'null');

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

        sleep(2);
        $this->warn('addPassengers group for SEARCH 1, SEARCH 2');
        $this->addPassengers($bookingId, $bookingItems, $bookingRateOrdinals, $this->query);

        $this->query = [];
        $bookingItem = $this->searchAndAddItem(3);
        $bookingId = $this->addBookingItem($bookingItem['booking_item'], $bookingId);
        $bookingItems2['search_3'] = $bookingItem['booking_item'];
        $bookingRateOrdinals2['search_3'] = $bookingItem['rate_ordinal'];

        $this->warn('addPassengers group for search_3');
        $this->addPassengers($bookingId, $bookingItems2, $bookingRateOrdinals2, $this->query);

        sleep(3);
        $this->warn('REMOVE ITEM');
        $this->removeBookingItem($bookingId, $bookingItem['booking_item']);

        $this->warn('RETRIEVE ITEMS');
        $this->retrieveItems($bookingId);

        sleep(3);
        $this->warn('BOOK ' . $bookingId);
        $this->book($bookingId, $bookingItems);
    }

    /**
     * @param array $responseData
     * @return string
     */
    private function getBookingItem(array $responseData): array|null
    {
        $flattened = Arr::dot($responseData);

        $bookingItems = [];
        foreach ($flattened as $key => $value) {
            if (str_contains($key, 'booking_item')
                && str_contains($key, $this->supplier)
                && $flattened[str_replace('booking_item', 'room_type', $key)] != 'Luxury'
                && $flattened[str_replace('booking_item', 'room_type', $key)] != 'STD'
            ) {
                $bookingItems[$key] = $value;
            }
        }
        if (empty($bookingItems)) return null;

        $randomKey = array_rand($bookingItems);
        return [
            'booking_item' => $bookingItems[$randomKey],
            'rate_ordinal' => $flattened[str_replace('booking_item', 'supplier_room_id', $randomKey)] ?? null,
        ];
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
        foreach (range(1, rand(1, 2)) as $index) {

            $room['adults'] = rand(1, 2);

            $children = rand(0, 2);
            $children_ages = [];
            if ($children > 0) {
                foreach (range(1, $children) as $index) {
                    $children_ages[] = rand(1, 15);
                }
                $room['children'] = $children;
                $room['children_ages'] = $children_ages;
            }

            $occupancy[] = $room;
        }

        $requestData = [
            'type' => 'hotel',
            'currency' => $faker->randomElement(['USD', 'EUR', 'GBP']),
            'destination' => $this->destination,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
            'rating' => $faker->numberBetween(3, 5),
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
        $requestData = ['booking_item' => $bookingItem];
        if ($bookingId !== null) $requestData['booking_id'] = $bookingId;

        $response = $this->client->post($this->url . '/api/booking/add-item', $requestData);
        $bookingId = $response->json()['data']['booking_id'];
        $this->info('booking_id = ' . $bookingId);

        return $bookingId;
    }

    /**
     * @param string $bookingId
     * @param array $bookingItems
     * @param array $bookingRateOrdinals
     * @param array $occupancy
     * @return void
     */
    private function addPassengers(string $bookingId, array $bookingItems, array $bookingRateOrdinals, array $occupancy): void
    {
        $faker = Faker::create();

        $requestData = ['passengers' => []];

        foreach ($bookingItems as $keySearch => $bookingItem) {

            $numberOfRates = explode(';', $bookingRateOrdinals[$keySearch]);

            foreach ($numberOfRates as $keyRoom => $rate) {

                if ($this->supplier === 'HBSI') {
                    $rate_occupancy = $rate;
                    $guest = explode('-', $rate_occupancy);
                    $adultsBI = $guest[0];
                    $childrenBI = $guest[1] + $guest[2];
                }

                $roomCounter = $keyRoom + 1;
                $step = 0;
                foreach ($occupancy[$keySearch] as $occupant) {

                    if ($this->supplier === 'HBSI') {
                        $children = 0;
                        $adults = $occupant['adults'];
                        if (isset($occupant['children_ages']) && count($occupant['children_ages']) > 0) {
                            $children = count($occupant['children_ages']);
                        }
                        if ($adultsBI !== $adults && $childrenBI !== $children) {
                            continue;
                        }
                        if ($step > 0) continue;
                    }

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
                    $step++;
                }
            }
        }

        $requestData['booking_id'] = $bookingId;
//        dump($requestData);

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
    private function book(string $bookingId, array $bookingItems): void
    {
        $faker = Faker::create();

        $requestData = [
            'booking_id' => $bookingId,
            'amount_pay' => 'Deposit',
            'booking_contact' => [
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => 'test@gmail.com',
                'phone' => [
                    'country_code' => '1',
                    'area_code' => '487',
                    'number' => '5550077',
                ],
                'address' => [
                    'line_1' => '5047 Kessler Glens', //$faker->streetAddress,
                    'city' => 'Ortizville', //$faker->city,
                    'state_province_code' => 'VT', //$faker->stateAbbr,
                    'postal_code' => 'mt', //$faker->lexify(str_repeat('?', rand(1, 7))), //$faker->postcode,
                    'country_code' => 'US', //$faker->countryCode,
                ],
            ],
        ];

        if ($this->supplier === 'HBSI') {
            foreach ($bookingItems as $item) {
                $cards[] = [
                    'credit_card' => [
                        'cvv' => 123,
                        'number' => 4001919257537193,
                        'card_type' => 'VISA',
                        'name_card' => 'Visa',
                        'expiry_date' => '09/2026',
                        'billing_address' => null
                    ],
                    'booking_item' => $item,
                ];
            }
            $requestData['credit_cards'] = $cards;
        }

        $response = $this->client->post($this->url . '/api/booking/book', $requestData);
        $this->info('book: ' . json_encode($response->json()));
    }
}
