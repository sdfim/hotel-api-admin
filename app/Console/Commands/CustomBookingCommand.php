<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CustomBookingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'custom-booking-command {step}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $client;
    protected const TOKEN = '3pzcngNyytS3KA7ebXaVX5rKMW5c06QdnLXo4ZSV595a1707';
    protected const BASE_URI = 'https://ddwlx1ki3fks2.cloudfront.net';

    // protected const TOKEN = '2x3WbYgBLcfkE8fS1WCUGeWRcEBLfVmY60agbnErb97f692a';
    // protected const BASE_URI = 'http://localhost:8008';
	 /**
     * @var string|null
     */
    private string|null $step;

    /**
     * Execute the console command.
     */
    public function handle()
    {
		$this->step = $this->argument('step'); // 1, 2, 3, 4

		foreach (range(1, $this->step) as $index) {
			$this->strategy1();
		}
	}


	public function strategy1(): void
	{	
        $this->warn('SEARCH 1');

        // Step 1
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/pricing/search',
            $this->searchRequest(2)
        );
        $responseData = $response->json();
        $searchId = $responseData['data']['search_id'];
        $bookingItem = $responseData['data']['results']['Expedia'][0]['room_groups'][0]['rooms'][0]['booking_item'];
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        // Step 2
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/add-item', ['booking_item' => $bookingItem]);
        $responseData = $response->json();
        $bookingId = $responseData['data']['booking_id'];
        $this->info('booking_id = ' . $bookingId);

        // Step 3
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/add-passengers',
            ['booking_id' => $bookingId, 'booking_item' => $bookingItem] + $this->addPassengersRequest(2)
        );
        $this->info('add-passengers = ' . json_encode($response->json()));

        # SEARCH 2
        $this->warn('SEARCH 2');

        // Step 1 for Search 2
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/pricing/search',
            $this->searchRequest(3)
        );
        $responseData = $response->json();
        $searchId = $responseData['data']['search_id'];
        $bookingItem = $responseData['data']['results']['Expedia'][0]['room_groups'][0]['rooms'][0]['booking_item'];
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        // Step 2 for Search 2
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/add-item',
            ['booking_item' => $bookingItem, 'booking_id' => $bookingId]
        );
        $responseData = $response->json();
        $bookingId = $responseData['data']['booking_id'];
        $this->info('booking_id = ' . $bookingId);

        // Step 3 for Search 2
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/add-passengers',
            ['booking_id' => $bookingId, 'booking_item' => $bookingItem] + $this->addPassengersRequest(3)
        );
        $this->info('add-passengers = ' . json_encode($response->json()));

		# SEARCH 3
        $this->warn('SEARCH 3');

        // Step 1 for Search 3
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/pricing/search',
            $this->searchRequest(2)
        );
        $responseData = $response->json();
        $searchId = $responseData['data']['search_id'];
        $bookingItem = $responseData['data']['results']['Expedia'][0]['room_groups'][0]['rooms'][0]['booking_item'];
        $this->info('search_id = ' . $searchId);
        $this->info('booking_item = ' . $bookingItem);

        // Step 2 for Search 3
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/add-item',
            ['booking_item' => $bookingItem, 'booking_id' => $bookingId]
        );
        $responseData = $response->json();
        $bookingId = $responseData['data']['booking_id'];
        $this->info('booking_id = ' . $bookingId);

        // Step 3 for Search 3
        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/add-passengers',
            ['booking_id' => $bookingId, 'booking_item' => $bookingItem] + $this->addPassengersRequest(2)
        );
        $this->info('add-passengers = ' . json_encode($response->json()));

		# Remove Item
		$this->warn('remove ITEM');

		$response = Http::withToken(self::TOKEN)->delete(
			self::BASE_URI . '/api/booking/remove-item',
			['booking_id' => $bookingId, 'booking_item' => $bookingItem]
		);
		$this->info('remove-item = ' . json_encode($response->json()));

        # RETRIEVE ITEMS
        $this->warn('RETRIEVE ITEMS');

        $response = Http::withToken(self::TOKEN)->get(
            self::BASE_URI . '/api/booking/retrieve-items',
            ['booking_id' => $bookingId]
        );
        $this->info('retrieve-items = ' . json_encode($response->json()));

        # BOOK
        $this->warn('BOOK');

        $response = Http::withToken(self::TOKEN)->post(
            self::BASE_URI . '/api/booking/book',
            ['booking_id' => $bookingId] + $this->addBookRequest()
        );
        $this->info('book = ' . json_encode($response->json()));
    }

    private function searchRequest($count = 1 ): array
    {
		$faker = Faker::create();

        $checkin = Carbon::now()->addDays(7)->toDateString();
        $checkout = Carbon::now()->addDays(7 + rand(2, 5))->toDateString();

		$occupancy = [];
		foreach (range(1, $count) as $index) {
			$occupancy[] = [
				"adults" => $faker->numberBetween(1, 3),
				"children" => ($count % 2 == 0) ? 1 : 0,
			];
		}

        return [
            "type" => "hotel",
            "checkin" => $checkin,
            "checkout" => $checkout,
            "destination" => $faker->randomElement([961, 302, 93, 960, 1102]),
            "rating" => $faker->numberBetween(3, 5),
            "occupancy" => $occupancy
        ];
    }

    private function addPassengersRequest($count = 1): array
    {
        $faker = Faker::create();

		$rooms = [];
		foreach (range(1, $count) as $index) {
			$rooms[] = [
				"given_name" => $faker->firstName,
				"family_name" => $faker->lastName,
			];
		}

        return [
            "title" => "mr",
            "first_name" => $faker->firstName,
            "last_name" => $faker->lastName,
            "rooms" => $rooms
        ];
    }



    private function addBookRequest(): array
    {
        return [
            "amount_pay" => "Deposit",
            "email" => "john@example.com",
            "phone" => [
                "country_code" => "1",
                "area_code" => "487",
                "number" => "5550077",
            ],
            "booking_contact" => [
                "given_name" => "John",
                "family_name" => "Smith",
                "address" => [
                    "line_1" => "555 1st St",
                    "city" => "Seattle",
                    "state_province_code" => "WA",
                    "postal_code" => "98121",
                    "country_code" => "US",
                ],
            ],
        ];
    }

}
