<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CustomBookingCommand extends Command
{
    protected $signature = 'custom-booking-command {step}';
    protected $description = 'Command description';
    protected $client;
    protected const TOKEN = 'bE38wDtILir6aJWeFHA2EnHZaQQcwdFjn7PKFz3A482bcae2';
    protected const BASE_URI = 'https://ddwlx1ki3fks2.cloudfront.net';

    // protected const TOKEN = '2x3WbYgBLcfkE8fS1WCUGeWRcEBLfVmY60agbnErb97f692a';
    // protected const BASE_URI = 'http://localhost:8008';

    private string $step;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(self::TOKEN);
    }

    public function handle()
    {
        $this->step = $this->argument('step');

        foreach (range(1, $this->step) as $index) {
			$this->warn('STEP ' . $index . ' of ' . $this->step);
            $this->strategy1();
        }
    }

	private function getBookingItem(array $responseData) : string
	{
		$flattened = Arr::dot($responseData);

		$bookingItems = [];
		$i = 0;
		foreach ($flattened as $key => $value) {
			if (strpos($key, 'booking_item') !== false) {
				$bookingItems[$i] = $value;
				$i++;
			}
		}
		
		return $bookingItems[rand(1, $i)];
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
        $faker = Faker::create();
        $checkin = Carbon::now()->addDays(7)->toDateString();
        $checkout = Carbon::now()->addDays(7 + rand(2, 5))->toDateString();

        $occupancy = [];
        foreach (range(1, $count) as $index) {

			$room["adults"] = $faker->numberBetween(1, 3);

			if ($count % 2 != 0) $children = rand(0, 2);
			else $children = 0;
			$children_ages = [];
			if ($children > 0) {
				foreach (range(1, $children) as $index) {
					$children_ages[] = rand(1, 17);
				}
				$room["children"] = $children;
				$room["children_ages"] = $children_ages;
			}

			$occupancy[] = $room;
        }

        $requestData = [
            "type" => "hotel",
			"destination" => $faker->randomElement([961, 302, 93, 960, 1102]),
            "checkin" => $checkin,
            "checkout" => $checkout,
            "occupancy" => $occupancy,
			"rating" => $faker->numberBetween(3, 5),
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

        $faker = Faker::create();
		$rooms = [];
		foreach (range(1, $count) as $index) {
			$rooms[] = [
				"given_name" => $faker->firstName,
				"family_name" => $faker->lastName,
			];
		}
        $requestData += [
            "title" => "mr",
            "first_name" => $faker->firstName,
            "last_name" => $faker->lastName,
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
		$faker = Faker::create();

        $requestData = [
            "booking_id" => $bookingId,
            "amount_pay" => "Deposit",
            "email" => $faker->email,
            "phone" => [
                "country_code" => "1",
                "area_code" => "487",
                "number" => "5550077",
            ],
            "booking_contact" => [
                "given_name" => $faker->firstName,
                "family_name" => $faker->lastName,
                "address" => [
                    "line_1" => $faker->streetAddress,
                    "city" => $faker->city,
                    "state_province_code" => $faker->stateAbbr,
                    "postal_code" => $faker->postcode,
                    "country_code" => $faker->countryCode,
                ],
            ],
		];

        $response = $this->client->post(self::BASE_URI . '/api/booking/book', $requestData);
		$this->info('book: ' . json_encode($response->json()));
    }
}