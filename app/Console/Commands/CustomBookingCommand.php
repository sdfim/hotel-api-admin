<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CustomBookingCommand extends Command
{
    use WithFaker;

    protected $signature = 'custom-booking-command {step}';
    protected $description = 'Command description';
    protected PendingRequest $client;
    protected const TOKEN = 'bE38wDtILir6aJWeFHA2EnHZaQQcwdFjn7PKFz3A482bcae2';
    protected const BASE_URI = 'https://ddwlx1ki3fks2.cloudfront.net';

//    protected const TOKEN = 'SqSDT1oa1OVRS6rl42N0xyYn3031HF8Tbnf0dnaLfb2abad1';
//    protected const BASE_URI = 'http://localhost:8008';

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(self::TOKEN);
    }

    public function handle(): void
    {
        $step = $this->argument('step');

        foreach (range(1, $step) as $index) {
            $this->warn('STEP ' . $index . ' of ' . $step);
            $this->strategy1();
        }
    }

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

    public function strategy1(): void
    {
        $this->warn('SEARCH 1');
        $responseData = $this->makeSearchRequest(2);
        $searchId = $responseData['data']['search_id'];
        $bookingItem = $this->getBookingItem($responseData);
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        $bookingId = $this->addBookingItem($bookingItem);
        $this->addPassengers($bookingId, $bookingItem, 2);

        $this->warn('SEARCH 2');
        $responseData = $this->makeSearchRequest(3);
        $searchId = $responseData['data']['search_id'];
        $bookingItem = $this->getBookingItem($responseData);
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        $bookingId = $this->addBookingItem($bookingItem, $bookingId);
        $this->addPassengers($bookingId, $bookingItem, 3);

        $this->warn('SEARCH 3');
        $responseData = $this->makeSearchRequest(2);
        $searchId = $responseData['data']['search_id'];
        $bookingItem = $this->getBookingItem($responseData);
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        $bookingId = $this->addBookingItem($bookingItem, $bookingId);
        $this->addPassengers($bookingId, $bookingItem, 2);

        $this->warn('REMOVE ITEM');
        $this->removeBookingItem($bookingId, $bookingItem);

        $this->warn('RETRIEVE ITEMS');
        $this->retrieveItems($bookingId);

        $this->warn('BOOK');
        $this->book($bookingId);
    }

    private function makeSearchRequest(int $count = 1): array
    {
        $checkin = Carbon::now()->addDays(7)->toDateString();
        $checkout = Carbon::now()->addDays(7 + rand(2, 5))->toDateString();

        $occupancy = [];
        foreach (range(1, $count) as $ignoredIndex) {
            $room["adults"] = $this->faker->numberBetween(1, 3);

            if ($count % 2 != 0) $children = rand(0, 2);
            else $children = 0;
            if ($children > 0) {
                $room["children"] = $children;
                $room["children_ages"] = array_map(function () {
                    return rand(1, 17);
                }, range(1, $children));
            }

            $occupancy[] = $room;
        }

        $requestData = [
            "type" => "hotel",
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'CAD', 'JPY']),
            "destination" => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            "checkin" => $checkin,
            "checkout" => $checkout,
            "occupancy" => $occupancy,
            "rating" => $this->faker->numberBetween(3, 5),
        ];

        $response = $this->client->post(self::BASE_URI . '/api/pricing/search', $requestData);

        return $response->json();
    }

    private function addBookingItem(string $bookingItem, ?string $bookingId = null): string
    {
        $requestData = [
            "booking_item" => $bookingItem,
        ];

        if ($bookingId !== null) {
            $requestData['booking_id'] = $bookingId;
        }

        $response = $this->client->post(self::BASE_URI . '/api/booking/add-item', $requestData);
        $bookingId = $response->json()['data']['booking_id'];
        $this->info('booking_id = ' . $bookingId);

        return $bookingId;
    }

    private function addPassengers(string $bookingId, string $bookingItem, int $count = 1): void
    {

        $requestData = [
            "booking_id" => $bookingId,
            "booking_item" => $bookingItem,
        ];


        $rooms = [];
        foreach (range(1, $count) as $ignoredIndex) {
            $rooms[] = [
                "given_name" => $this->faker->firstName,
                "family_name" => $this->faker->lastName,
            ];
        }
        $requestData += [
            "title" => "mr",
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "rooms" => $rooms
        ];

        $response = $this->client->post(self::BASE_URI . '/api/booking/add-passengers', $requestData);
        $this->info('addPassengers: ' . json_encode($response->json()));
    }

    private function removeBookingItem(string $bookingId, string $bookingItem): void
    {
        $requestData = [
            "booking_id" => $bookingId,
            "booking_item" => $bookingItem,
        ];

        $response = $this->client->delete(self::BASE_URI . '/api/booking/remove-item', $requestData);
        $this->info('removeBookingItem: ' . json_encode($response->json()));
    }

    private function retrieveItems(string $bookingId): void
    {
        $requestData = [
            "booking_id" => $bookingId,
        ];

        $response = $this->client->get(self::BASE_URI . '/api/booking/retrieve-items', $requestData);
        $this->info('retrieveItems: ' . json_encode($response->json()));
    }

    private function book(string $bookingId): void
    {
        $requestData = [
            "booking_id" => $bookingId,
            "amount_pay" => "Deposit",
            "email" => $this->faker->email,
            "phone" => [
                "country_code" => "1",
                "area_code" => "487",
                "number" => "5550077",
            ],
            "booking_contact" => [
                "given_name" => $this->faker->firstName,
                "family_name" => $this->faker->lastName,
                "address" => [
                    "line_1" => $this->faker->streetAddress,
                    "city" => $this->faker->city,
                    "state_province_code" => $this->faker->stateAbbr,
                    "postal_code" => $this->faker->postcode,
                    "country_code" => $this->faker->countryCode,
                ],
            ],
        ];

        $response = $this->client->post(self::BASE_URI . '/api/booking/book', $requestData);
        $this->info('book: ' . json_encode($response->json()));
    }
}
