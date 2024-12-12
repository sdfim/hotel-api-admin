<?php

namespace Tests\Feature\Console\Commands\RequestFlowTests;

use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class FlowHbsiCangeBookTest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:hbsi-change-book-test {run_firt_endpoints?} {booking_id?} {booking_item?}';

    protected PendingRequest $client;

    protected string $url;
    private ?string $run_firt_endpoints;
    private ?string $booking_id;
    private ?string $booking_item;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST', 'http://localhost:8000');
    }

    public function handle(): void
    {
        $this->run_firt_endpoints= $this->argument('run_firt_endpoints');
        if ($this->run_firt_endpoints == 1) {
            Artisan::call('hbsi-book-flow-test');
            $output = Artisan::output();
            $this->info($output);
        }

        $this->booking_id = $this->argument('booking_id');
        $this->booking_item = $this->argument('booking_item');

        if (!$this->booking_id || !$this->booking_item) {
            $bookingInspector = ApiBookingInspectorRepository::getLastBooked();
            $this->booking_id = $bookingInspector->booking_id;
            $this->booking_item = $bookingInspector->booking_item;
        }

        $this->info('booking_id: '.$this->booking_id);
        $this->info('booking_item: '.$this->booking_item);

        $this->flow();
    }

    public function flow(): void
    {
        $this->info('------------------------------------');
        $availableEndpointsList = $this->availableEndpoints();
        $this->info('availableEndpoints: '.json_encode($availableEndpointsList));

        $this->info('------------------------------------');
        $responseSoftChange = $this->softChange();
        $this->info('softChange: '.json_encode($responseSoftChange));

        $this->info('------------------------------------');
        $responseAvailability = $this->availability();
        $this->info('softChange result : '.json_encode([
            'success' => Arr::get($responseAvailability,'success'),
                    'message' => Arr::get($responseAvailability,'message'),
                    'change_search_id' => Arr::get($responseAvailability,'data.change_search_id'),
                    ]
            ));

        $this->info('------------------------------------');
        $new_booking_item = Arr::get($responseAvailability,'data.change_search_id', false)
            ? $this->getBookingItem($responseAvailability)
            : Uuid::uuid4()->toString();
        $this->info('$new_booking_item: '.$new_booking_item);

        $this->info('------------------------------------');
        $responsePriceCheck = $this->priceCheck($new_booking_item);
        $this->info('priceCheck: '.json_encode($responsePriceCheck));

        $this->info('------------------------------------');
        $responseHardChange = $this->hardChange($new_booking_item);
        $this->info('hardChange: '.json_encode($responseHardChange));

        $this->info('------------------------------------');
        $responseRetrieveItems = $this->retrieveBooking($this->booking_id);
        $this->info('retrieveBooking: '.json_encode($responseRetrieveItems));
    }

    private function availableEndpoints()
    {
        $response = $this->client->get($this->url.'/api/booking/change/available-endpoints/?booking_item='.$this->booking_item);

        $listEndpoints = $response->json()['data']['endpoints'];

        return $listEndpoints;

    }

    private function softChange()
    {
        $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy($this->booking_item);
        $rooms = explode(';', $roomsBookingItem);

        $passengers = [];
        $special_requests = [];
        $faker = Faker::create();

        foreach ($rooms as $k => $room) {
            $adults = explode('-', $room)[0];
            for ($i = 0; $i < $adults; $i++) {
                $passengers[] = [
                    'title' => 'mr',
                    'given_name' => $faker->firstName,
                    'family_name' => $faker->lastName,
                    'room' => $k + 1,
                ];
                if (empty($special_requests)) {
                    $special_requests[] = [
                        'special_request' => $faker->sentence,
                        'room' => $k + 1,
                    ];
                }
            }
        }

        $params = [
            'booking_id' => $this->booking_id,
            'booking_item' => $this->booking_item,
            'passengers' => $passengers,
            'special_requests' => $special_requests,
        ];

        $this->warn('softChange params : '.json_encode($params));

        $response = $this->client->put($this->url.'/api/booking/change/soft-change', $params);

        return $response->json();
    }

    private function availability()
    {
        $occupancy = [];
        foreach (range(1, 2) as $index) {
            $room['adults'] = rand(1, 2);
            $occupancy[] = $room;
        }

        $params = [
            'booking_id' => $this->booking_id,
            'booking_item' => $this->booking_item,
            'page' => 1,
            'results_per_page' => 10,
            'checkin' => Carbon::now()->addDays(150)->format('Y-m-d'),
            'checkout' => Carbon::now()->addDays(150 + rand(2, 5))->format('Y-m-d'),
            'occupancy' => $occupancy,
        ];

        $this->warn('availability params : '.json_encode($params));

        $response = $this->client->post($this->url.'/api/booking/change/availability', $params);

        return $response->json();
    }

    private function priceCheck(string $new_booking_item)
    {
        $params = [
            'booking_item' => $this->booking_item,
            'new_booking_item' => $new_booking_item,
            'booking_id' => $this->booking_id,
        ];

        $this->warn('priceCheck params : '.json_encode($params));

        $response = $this->client->get($this->url.'/api/booking/change/price-check', $params);

        return $response->json();
    }

    private function hardChange($new_booking_item)
    {
        $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy($new_booking_item);
        $rooms = $roomsBookingItem ? explode(';', $roomsBookingItem) : [];

        $passengers = [];
        $special_requests = [];
        $faker = Faker::create();

        foreach ($rooms as $k => $room) {
            $adults = explode('-', $room)[0];
            for ($i = 0; $i < $adults; $i++) {
                $passengers[] = [
                    'title' => 'mr',
                    'given_name' => $faker->firstName,
                    'family_name' => $faker->lastName,
                    'date_of_birth' => $faker->date,
                    'room' => $k + 1,
                ];
                if (empty($special_requests)) {
                    $special_requests[] = [
                        'special_request' => $faker->sentence,
                        'room' => $k + 1,
                    ];
                }
            }
        }

        if (empty($passengers)) {
            $passengers[] = [
                'title' => 'mr',
                'given_name' => $faker->firstName,
                'family_name' => $faker->lastName,
                'date_of_birth' => $faker->date,
                'room' => 1,
            ];
        }

        $params = [
            'new_booking_item' => $new_booking_item,
            'booking_id' => $this->booking_id,
            'booking_item' => $this->booking_item,
            'passengers' => $passengers,
            'special_requests' => $special_requests,
        ];

        $this->warn('hardChange params : '.json_encode($params));

        $response = $this->client->put($this->url.'/api/booking/change/hard-change', $params);

        return $response->json();
    }


    private function retrieveBooking(string $bookingId): ?array
    {
        $params = [
            'booking_id' => $bookingId,
        ];

        $response = $this->client->get($this->url.'/api/booking/retrieve-booking', $params);
        return $response->json();
    }

    private function getBookingItem(array $responseData): ?string
    {
        $flattened = Arr::dot($responseData);

        $countRooms = count($responseData['data']['query']['occupancy']);

        $bookingItems = [];
        if ($countRooms === 1) {
            foreach ($flattened as $key => $value) {
                if (str_contains($key, 'booking_item')) {
                    $bookingItems[$key] = $value;
                }
            }
        } else {
            foreach ($flattened as $key => $value) {
                if (str_contains($key, 'room_combinations')) {
                    $bookingItems[$key] = explode('.', $key)[4];
                }
            }
        }

        if (empty($bookingItems)) {
            return null;
        }
        $randomKey = array_rand($bookingItems);
        $booking_item = $bookingItems[$randomKey];

        return $booking_item;
    }

}
