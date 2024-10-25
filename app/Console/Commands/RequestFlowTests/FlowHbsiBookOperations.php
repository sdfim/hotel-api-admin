<?php

namespace App\Console\Commands\RequestFlowTests;

use App\Repositories\ApiBookingItemRepository;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class FlowHbsiBookOperations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:hbsi-book-operations {scenarios?} {destination?} {type?}';
    protected PendingRequest $client;

    protected string $url;

    private ?string $destination;
    private ?array $query;
    private ?string $type;
    private ?string $supplier;
    private ?int $daysAfter;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST', 'http://localhost:8000');
    }

    public function handle(): void
    {
        $this->preset();

        $scenariosToRun = $this->argument('scenarios')
            ? array_map('trim', explode(',', $this->argument('scenarios')))
            : [
                'scenario_1',
                'scenario_2',
                'scenario_3',
                'scenario_4',
                'scenario_5',
                'scenario_6',
                'scenario_7',
                'scenario_8',
            ];

        $this->runScenarios($scenariosToRun);

        /**
         * #########################
         * Scenario #1 (One Room, HardChange)
         *
         * Book Room Only with 1 Adult for 5 nights for Initial test
         * Modify Reservation from Scenario #1 and Change the Arrival Date
         * ReadRQ- Retrive booking modification details from DG
         * Cancel Reservation from Scenario #1
         *
         * #########################
         * Scenario #2 (One Room, HardChange)
         *
         * Book Room Only with 2 Adult for 5 nights
         * Modify Reservation from Scenario #1 and Change the Departure date Date
         * Cancel Reservation from above scenario #2
         *
         * #########################
         * Scenario #3 (One Room, SoftChange)
         *
         * Book Room Only with 2 Adult  and 1 infant,1 child  and 1 teenager for 3 nights - verify Child rate
         * Modify above booking and change the adult name
         * ReadRQ- Retrive booking modification details from DG
         * Modify the above booking and  add 1 more adults to the same booking
         * Modify the above booking and  cancel the added adult
         * Cancel Reservation from above scenario #3
         *
         * #########################
         * Scenario #4 (Two Room, HardChange)
         *
         * Book Room Only with 4 Adults and two rooms for 5 nights
         * Modify Reservation from the above scenarios and Delete one room and 2 Adults
         * Cancel Reservation from the above Scenario #4
         *
         * #########################
         * Scenario #5 (Two Room, HardChange)
         *
         * Book 2 rooms with 2 different occupancies. 1 adult and 1 child in one room and 3 adults in second room
         * Modify Reservation from above Scenario and Add 1 adult, 1 child and one additional room
         * Cancel Reservation from the above Scenario #5
         *
         * #########################
         * Scenario #6 (Two Room, HardChange)
         *
         * Book 2 rooms with 2 different room types 1 adult and 1 child in each room
         * Modify Reservation from above Scenario and delete 1 adult, 1 child and one additional room
         * Cancel Reservation from the above Scenario #6
         *
         * #########################
         * Scenario #7 (One Room, SoftChange)
         *
         * Book 1 rooms with 2 adults
         * Modify reservation by adding a comment
         * Cancel Reservation from above scenario #7
         *
         * #########################
         * Scenario #8 (One Room, SoftChange)
         *
         * Book 1 rooms with 2 adults
         * Modify reservation by adding a special request
         * Cancel Reservation from above scenario #8
         */
    }

    private function runScenarios(array $scenarios): void
    {
        foreach ($scenarios as $scenario) {
            $methodName = 'scenario_' . $scenario;
            if (method_exists($this, $methodName)) {
                $this->$methodName();
            } else {
                $this->warn("Scenario method $methodName does not exist.");
            }
        }
    }

    private function scenario_1(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #1');
        $occupancy = [['adults' => 1]];
        $nights = 5;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights + 1)->toDateString();
        $this->flowHardCange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        $this->cancel($bookingId);
    }

    private function scenario_2(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #2');
        $occupancy = [['adults' => 2]];
        $nights = 5;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);
        $checkin = Carbon::now()->addDays($this->daysAfter + $nights + 1)->toDateString();
        $this->flowHardCange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        $this->cancel($bookingId);
    }

    private function scenario_3(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #3');
        $occupancy = [['adults' => 2, 'children_ages' => [1, 5, 15]]];
//        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);

        $this->softChange($bookingId, $bookingItem);
        $this->cancel($bookingId);
    }

    private function scenario_4(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #4');
        $occupancy = [['adults' => 2], ['adults' => 2]];
        $nights = 5;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);

        $occupancy = [['adults' => 2]];
        $this->flowHardCange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        $this->cancel($bookingId);
    }

    private function scenario_5(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #5');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [5]],
            ['adults' => 3]
        ];
        $nights = 5;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);

        $occupancy = [
            ['adults' => 2, 'children_ages' => [5, 7]],
            ['adults' => 3]
        ];
        $this->flowHardCange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        $this->cancel($bookingId);
    }

    private function scenario_6(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #6');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [1]],
            ['adults' => 1, 'children_ages' => [1]]
        ];
        $nights = 5;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);

        $occupancy = [['adults' => 1, 'children_ages' => [1]]];
        $this->flowHardCange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        $this->cancel($bookingId);
    }

    private function scenario_7(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #7');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);

        $faker = Faker::create();
        $newComment[] = [
            'booking_item' => $bookingItem,
            'comment' => $faker->sentence,
            'room' => 1,
        ];
        $this->softChange($bookingId, $bookingItem, [], $newComment);
        $this->cancel($bookingId);
    }

    private function scenario_8(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #8');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $nights, $checkin, $checkout);

        $faker = Faker::create();
        $newSpecialRequests[] = [
            'special_request' => $faker->sentence,
            'room' => 1,
        ];
        $this->softChange($bookingId, $bookingItem, $newSpecialRequests, []);
        $this->cancel($bookingId);
    }


    private function processBooking(array $occupancy, int $nights, string $checkin, string $checkout): array
    {
        $searchResponse = $this->search($occupancy, $nights, $checkin, $checkout);
        $bookingItem = $this->fetchBookingItem($searchResponse);
        $bookingId = $this->addBookingItem($bookingItem);
        $this->addPassengers($bookingId, [$bookingItem], [$occupancy]);
        $this->book($bookingId, [$bookingItem]);

        return [$bookingId, $bookingItem];
    }

    private function preset(): void
    {
        $this->destination= $this->argument('destination') ?? '508';
        $this->type= $this->argument('type') ?? 'test';
        $this->supplier = 'HBSI';
        if ($this->type !== 'test') $this->daysAfter = 240;
        else $this->daysAfter = 20;
    }

    /**
     * Search for hotels.
     *
     * @param array $occupancy Array of rooms, where each room is an associative array with keys:
     *                        - 'adults' (int): Number of adults in the room.
     *                        - 'children_ages' (array): Optional array of integers representing the ages of the children in the room.
     * @param int $nights Number of nights for the stay.
     * @throws ConnectionException
     */
    private function search(array $occupancy, int $nights, string $checkin, string $checkout): array
    {
        $faker = Faker::create();

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

        $searchId = Arr::get($response->json(), 'data.search_id');
        $this->info('Search ID: '.$searchId);

        return $response->json();
    }

    private function fetchBookingItem(array $searchResponse): ?string
    {
        $results = Arr::get($searchResponse, 'data.results');

        foreach ($results as $hotel) {
            $roomCombinations = Arr::get($hotel, 'room_combinations');
            $roomGroups = Arr::get($hotel, 'room_groups');

            $bookingItemNonRefundableMap = [];

            foreach ($roomGroups as $group) {
                foreach ($group['rooms'] as $room) {
                    $bookingItem = $room['booking_item'];
                    $nonRefundable = $room['non_refundable'];
                    $bookingItemNonRefundableMap[$bookingItem] = $nonRefundable;
                }
            }

            foreach ($roomCombinations as $parentId => $childIds) {
                $allNonRefundable = true;

                foreach ($childIds as $childId) {
                    if ($bookingItemNonRefundableMap[$childId] !== false) {
                        $allNonRefundable = false;
                        break;
                    }
                }

                if ($allNonRefundable) {
                    $this->info('Booking ITEM: '.$bookingItem);
                    return $parentId;
                }
            }
        }

        return null;
    }

    private function addBookingItem(string $bookingItem, ?string $bookingId = null): string
    {
        $requestData = ['booking_item' => $bookingItem];
        if ($bookingId !== null) {
            $requestData['booking_id'] = $bookingId;
        }

        $response = $this->client->post($this->url.'/api/booking/add-item', $requestData);
        $bookingId = $response->json()['data']['booking_id'];

        $this->info('Booking ID: '.$bookingId);

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

//        $this->info('addPassengers: '.json_encode($response->json()));
        $response->json() ? $this->info('addPassengers success') : $this->error('addPassengers failed');
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
                    'booking_item' => $bookingItems[0],
                    'room' => 1,
                    'special_request' => 'UJV Test Booking, please disregard.',
                ],
            ],
        ];

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


        $response = $this->client->post($this->url.'/api/booking/book', $requestData);
        $this->info('book: '.json_encode($response->json()));
    }

    private function cancel(string $bookingId): void
    {
        $requestData = [
            'booking_id' => $bookingId,
        ];

        $response = $this->client->delete($this->url . '/api/booking/cancel-booking', $requestData);

        if ($response->successful()) {
            $this->info('Cancelled booking: ' . $bookingId);
        } else {
            $this->error('Failed to cancel booking: ' . $bookingId);
        }
    }

    private function flowHardCange(string $bookingId, string $bookingItem, array $occupancy, string $checkin, string $checkout)
    {
        $this->info('------------------------------------');
        $responseAvailability = $this->availability($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        $this->info('softChange result : '.json_encode([
                    'success' => Arr::get($responseAvailability,'success'),
                    'message' => Arr::get($responseAvailability,'message'),
                    'change_search_id' => Arr::get($responseAvailability,'data.change_search_id'),
                ]
            ));

        $this->info('------------------------------------');
        $newBookingItem = Arr::get($responseAvailability,'data.change_search_id', false)
            ? $this->getBookingItem($responseAvailability)
            : Uuid::uuid4()->toString();
        $this->info('$new_booking_item: '.$newBookingItem);

        $this->info('------------------------------------');
        $responsePriceCheck = $this->priceCheck($bookingId, $bookingItem, $newBookingItem);
        $this->info('priceCheck: '.json_encode($responsePriceCheck));

        $this->info('------------------------------------');
        $responseHardChange = $this->hardChange($bookingId, $bookingItem, $newBookingItem);
        $this->info('hardChange: '.json_encode($responseHardChange));

        $this->info('------------------------------------');
        $responseRetrieveItems = $this->retrieveBooking($bookingId);
        $this->info('retrieveBooking: '.json_encode($responseRetrieveItems));
    }

    private function availableEndpoints($bookingItem)
    {
        $response = $this->client->get($this->url.'/api/booking/change/available-endpoints/?booking_item='.$bookingItem);

        $listEndpoints = $response->json()['data']['endpoints'];

        return $listEndpoints;
    }

    private function softChange(string $bookingId, string $bookingItem, array $specialRequests = [], array $comments = []): array
    {
        $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy($bookingItem);
        $rooms = explode(';', $roomsBookingItem);

        $passengers = [];
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
            }
        }

        $params = [
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
            'passengers' => $passengers,
        ];

        if (!empty($specialRequests)) {
            $params['special_requests'] = $specialRequests;
        }

        if (!empty($comments)) {
            $params['comments'] = $comments;
        }

        $this->warn('softChange params : '.json_encode($params));

        $response = $this->client->put($this->url.'/api/booking/change/soft-change', $params);

        $this->info('softChange: '.json_encode($response->json()));

        return $response->json();
    }

    private function availability(string $bookingId, string $bookingItem, array $occupancy, string $checkin, string $checkout): array
    {
        $params = [
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
            'page' => 1,
            'results_per_page' => 10,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
        ];

        $this->warn('availability params : '.json_encode($params));

        $response = $this->client->post($this->url.'/api/booking/change/availability', $params);

        return $response->json();
    }

    private function priceCheck(string $bookingId, string $bookingItem, string $newBookingItem)
    {
        $params = [
            'booking_item' => $bookingItem,
            'new_booking_item' => $newBookingItem,
            'booking_id' => $bookingId,
        ];

        $this->warn('priceCheck params : '.json_encode($params));

        $response = $this->client->get($this->url.'/api/booking/change/price-check', $params);

        return $response->json();
    }

    private function hardChange(string $bookingId, string $bookingItem, string $newBookingItem)
    {
        $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy($newBookingItem);
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
            'new_booking_item' => $newBookingItem,
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
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
