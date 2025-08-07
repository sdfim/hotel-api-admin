<?php

namespace App\Console\Commands\RequestFlowTests;

use App\Repositories\ApiBookingItemRepository;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class FlowHotelTraderBookDiffScenarios extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:htrader-book-diff-scenarios {scenarios?} {checkin?} {giata_id?}';

    protected PendingRequest $client;

    protected string $url;

    private ?string $checkin;

    private ?int $giata_id;

    private ?string $supplier;

    private ?int $daysAfter;

    protected bool $isQueueSync;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HOTEL_TRADER_BOOK_TEST');
        $this->isQueueSync = config('queue.default') === 'sync';
    }

    public function handle(): void
    {
        $this->preset();
        Artisan::call('cache:clear');

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
            ];

        $this->runScenarios($scenariosToRun);

        /**
         * #########################
         * Scenario #1
         *
         * Book Room Only with 2 Adults for 5 days for Initial test
         * Modify Reservation from Scenario #1 and Change the Arrival Date
         * Verify the ReadRQ return the booking Details
         * Cancel Reservation from Scenario #1
         * 51721;Aug 5 - 10
         * BAR/Double
         *
         * #########################
         * Scenario #2
         *
         * Book Room Only with 2 Adult for 5 nights
         * Cancel Reservation from above scenario #2
         * 51721;Aug 5 - 10
         * Promo/Double
         *
         * #########################
         * Scenario #3
         *
         * Book Room Only with 2 Adults, 1 Child, 1 Teen, and 1 Infant for two rooms for 2 days
         * Verify rates by person if policy is applied  (This Scenario of Child, Teen and/or Infant only apply if Partner supports these age categories)
         * Cancel Reservation from above scenario #3
         * 51721;Aug 15 - 17
         * BAR/Suite
         *
         * #########################
         * Scenario #4
         *
         * Book Room Only with 2 Adults with Comments and/or Special Requests (if Partner Supports)
         * Cancel Reservation from the above Scenario #4
         * Use 51721
         *
         * #########################
         * Scenario #5
         *
         * Book 2 rooms with 2 different room types 1 adult and 1 child in each room
         * Cancel Reservation from the above Scenario #5
         * 51721;Book Double and Suite
         *
         * #########################
         * Scenario #6
         *
         * Book 2 rooms with 2 different rate plans 1 adult and 1 child in each room
         * Cancel Reservation from the above Scenario #6
         * 51721;Book BAR and Promo
         *
         * #########################
         * Scenario #7
         *
         * Partial Cancellation in multi room booking
         * Cancel Reservation from above scenario #7 only one room
         *
         *  #########################
         *  Scenario #8
         *
         * Book Room for 2 Adults with included mealplan as All inclusive
         * 51721; Best /Suite
         * Aug 25-28
         *
         * #########################
         *   Scenario #9
         *
         * Book Room with 1 Adults and one Child to tested with additional mealplan "Breakfast" with additional rate
         * 51721;BAR /Suite
         * Aug 25-28
         */
    }

    private function runScenarios(array $scenarios): void
    {
        foreach ($scenarios as $scenario) {
            $methodName = 'scenario_'.$scenario;
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
        $occupancy = [['adults' => 2]];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
                'room_type' => 'Double',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $checkin = Carbon::parse($checkin)->addDays(1)->toDateString();
        $this->flowHardChange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);

        // $this->cancel($bookingId);

        // sleep(2);

        // $this->retrieveBooking($bookingId);
    }

    private function scenario_2(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #2');
        $occupancy = [['adults' => 2]];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'HTREN3',
                'room_type' => 'DLX0001K',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
    }

    private function scenario_3(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #3');
        // Book Room Only with 2 Adults, 1 Child, 1 Teen, and 1 Infant for two rooms for 2 days
        $occupancy = [
            ['adults' => 2, 'children_ages' => [5, 1]], // 5: child, 1: infant
        ];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
                'room_type' => 'Suite',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
    }

    private function scenario_4(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #4');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout);

        // $this->cancel($bookingId);
    }

    private function scenario_5(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #5');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [1]],
            ['adults' => 1, 'children_ages' => [1]],
        ];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
                'room_type' => 'Double',
            ],
            [
                'rate_name' => 'BAR',
                'room_type' => 'Suite',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
    }

    private function scenario_6(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #6');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [1]],
            ['adults' => 1, 'children_ages' => [1]],
        ];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
            ],
            [
                'rate_name' => 'Promo',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
    }

    private function scenario_7(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #7');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout);

        $this->info('------------------------------------');

        $occupancy2 = [['adults' => 1, 'children_ages' => [5]]];
        $checkin2 = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout2 = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy2, $checkin2, $checkout2, [], null, $bookingId);

        // $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_8(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #8');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'Best',
                'room_type' => 'Suite',
                'meal_plan' => 'All Inclusive',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        // $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_9(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #9');
        $occupancy = [['adults' => 1, 'children_ages' => [5]]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
                'room_type' => 'Suite',
                'meal_plan' => 'Breakfast',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        // $this->cancel($bookingId, $bookingItem);
    }

    private function findBookingItemByRoomParams(array $searchResponse, array $roomParamsArray): ?string
    {
        $results = Arr::get($searchResponse, 'data.results');
        if (! $results) {
            return null;
        }

        foreach ($results as $hotel) {
            $roomCombinations = Arr::get($hotel, 'room_combinations');
            $roomGroups = Arr::get($hotel, 'room_groups');
            if (! $roomCombinations || ! $roomGroups) {
                continue;
            }

            // Сопоставление booking_item => room params
            $bookingItemParamsMap = [];
            foreach ($roomGroups as $group) {
                foreach ($group['rooms'] as $room) {
                    $bookingItem = $room['booking_item'];
                    $params = [
                        'room_type' => $room['room_type'] ?? null,
                        'rate_name' => $room['rate_name'] ?? null,
                        'meal_plan' => $room['meal_plan'] ?? null,
                    ];
                    $bookingItemParamsMap[$bookingItem] = $params;
                }
            }

            foreach ($roomCombinations as $parentId => $childIds) {
                if (count($childIds) !== count($roomParamsArray)) {
                    continue;
                }
                $matched = true;
                foreach ($childIds as $idx => $childId) {
                    $expected = $roomParamsArray[$idx];
                    $actual = $bookingItemParamsMap[$childId] ?? [];
                    foreach ($expected as $key => $val) {
                        if (! isset($actual[$key]) || $actual[$key] != $val) {
                            $matched = false;
                            break 2;
                        }
                    }
                }
                if ($matched) {
                    $this->info('Booking ITEM: '.$parentId);

                    return $parentId;
                }
            }
        }

        return null;
    }

    private function processBooking(array $occupancy, string $checkin, string $checkout, array $roomParamsArray = [], ?string $inputBookingId = null): array|bool
    {
        $searchResponse = $this->search($occupancy, $checkin, $checkout);

        $bookingItem = null;
        if (! empty($roomParamsArray)) {
            $bookingItem = $this->findBookingItemByRoomParams($searchResponse, $roomParamsArray);
        } else {
            $bookingItem = $this->fetchBookingItem($searchResponse);
        }

        if (! $bookingItem) {
            $this->error('Booking item not found by given room params');
            exit(1);
        }
        $this->handleSleep();
        $bookingId = $this->addBookingItem($bookingItem, $inputBookingId);

        $this->handleSleep();
        $responseAddPassengers = $this->addPassengers($bookingId, [$bookingItem], [$occupancy]);
        if (Arr::get($responseAddPassengers, 'error')) {
            $this->error('Adding passengers failed');
            exit(1);
        }

        $this->handleSleep();
        $responseBook = $this->book($bookingId, [$bookingItem]);
        if (Arr::get($responseBook, 'error')) {
            $this->error('Booking failed');
            exit(1);
        }
        sleep(5);

        return [$bookingId, $bookingItem];
    }

    private function preset(): void
    {
        $this->checkin = $this->argument('checkin') ?? null;
        $this->giata_id = $this->argument('giata_id') ?? null;
        $this->supplier = 'HotelTrader';
        $this->daysAfter = $this->checkin ? (abs(Carbon::parse($this->checkin)->diffInDays(Carbon::now())) + 20) : 240;
    }

    /**
     * Search for hotels.
     *
     * @param  array  $occupancy  Array of rooms, where each room is an associative array with keys:
     *                            - 'adults' (int): Number of adults in the room.
     *                            - 'children_ages' (array): Optional array of integers representing the ages of the children in the room.
     *
     * @throws ConnectionException
     */
    private function search(array $occupancy, string $checkin, string $checkout): array
    {
        $faker = Faker::create();

        $requestData = [
            'type' => 'hotel',
            'giata_ids' => [$this->giata_id ?? 0],
            'supplier' => $this->supplier,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
            'results_per_page' => 100,
        ];

        if ($this->giata_id) {
            $requestData['giata_ids'] = [$this->giata_id];
        }

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

                //                foreach ($childIds as $childId) {
                //                    if ($bookingItemNonRefundableMap[$childId] !== false) {
                //                        $allNonRefundable = false;
                //                        break;
                //                    }
                //                }

                if ($allNonRefundable) {
                    $this->info('Booking ITEM: '.$bookingItem);

                    return $parentId;
                }
            }
        }

        return null;
    }

    private function fetchBookingItemWithDifferentRoomTypes(array $searchResponse, array $difRoomType): ?string
    {
        $results = Arr::get($searchResponse, 'data.results');

        foreach ($results as $hotel) {
            $roomCombinations = Arr::get($hotel, 'room_combinations');
            $roomGroups = Arr::get($hotel, 'room_groups');

            $bookingItemNonRefundableMap = [];
            $roomTypeMap = [];

            foreach ($roomGroups as $group) {
                foreach ($group['rooms'] as $room) {
                    $bookingItem = $room['booking_item'];
                    $nonRefundable = $room['non_refundable'];
                    $roomType = $room['room_type'];
                    $bookingItemNonRefundableMap[$bookingItem] = $nonRefundable;
                    $roomTypeMap[$bookingItem] = $roomType;
                }
            }

            foreach ($roomCombinations as $parentId => $childIds) {
                $allNonRefundable = true;
                $roomTypes = [];

                foreach ($childIds as $childId) {
                    if ($bookingItemNonRefundableMap[$childId] !== false) {
                        $allNonRefundable = false;
                        break;
                    }
                    $roomTypes[] = $roomTypeMap[$childId];
                }

                if ($allNonRefundable && count(array_unique($roomTypes)) === count($roomTypes) && ! array_diff($difRoomType, $roomTypes)) {
                    $this->info('Booking ITEM: '.$parentId);

                    return $parentId;
                }
            }
        }

        return null;
    }

    private function fetchAnyBookingItemWithDifferentRoomTypes(array $searchResponse, array $difRoomType): ?string
    {
        $results = Arr::get($searchResponse, 'data.results');

        foreach ($results as $hotel) {
            $roomCombinations = Arr::get($hotel, 'room_combinations');
            $roomGroups = Arr::get($hotel, 'room_groups');

            $bookingItemNonRefundableMap = [];
            $roomTypeMap = [];

            foreach ($roomGroups as $group) {
                foreach ($group['rooms'] as $room) {
                    $bookingItem = $room['booking_item'];
                    $nonRefundable = $room['non_refundable'];
                    $roomType = $room['room_type'];
                    $bookingItemNonRefundableMap[$bookingItem] = $nonRefundable;
                    $roomTypeMap[$bookingItem] = $roomType;
                }
            }

            foreach ($roomCombinations as $parentId => $childIds) {
                $allNonRefundable = true;
                $roomTypes = [];

                foreach ($childIds as $childId) {
                    //                    if ($bookingItemNonRefundableMap[$childId] !== false) {
                    //                        $allNonRefundable = false;
                    //                        break;
                    //                    }
                    $roomTypes[] = $roomTypeMap[$childId];
                }

                if ($allNonRefundable && count(array_unique($roomTypes)) === count($roomTypes) && ! array_diff($difRoomType, $roomTypes)) {
                    $this->info('Booking ITEM: '.$parentId);

                    return $parentId;
                }
            }
        }

        return null;
    }

    private function fetchAnyBookingItemWithDifferentRateTypes(array $searchResponse, array $difRateType): ?string
    {
        $results = Arr::get($searchResponse, 'data.results');

        foreach ($results as $hotel) {
            $roomCombinations = Arr::get($hotel, 'room_combinations');
            $roomGroups = Arr::get($hotel, 'room_groups');

            $bookingItemNonRefundableMap = [];
            $rateTypeMap = [];

            foreach ($roomGroups as $group) {
                foreach ($group['rooms'] as $room) {
                    $bookingItem = $room['booking_item'];
                    $nonRefundable = $room['non_refundable'];
                    $rateType = $room['rate_name'];
                    $bookingItemNonRefundableMap[$bookingItem] = $nonRefundable;
                    $rateTypeMap[$bookingItem] = $rateType;
                }
            }

            foreach ($roomCombinations as $parentId => $childIds) {
                $allNonRefundable = true;
                $rateTypes = [];

                foreach ($childIds as $childId) {
                    //                    if ($bookingItemNonRefundableMap[$childId] !== false) {
                    //                        $allNonRefundable = false;
                    //                        break;
                    //                    }
                    $rateTypes[] = $rateTypeMap[$childId];
                }

                if ($allNonRefundable && count(array_unique($rateTypes)) === count($rateTypes) && ! array_diff($difRateType, $rateTypes)) {
                    $this->info('Booking ITEM: '.$parentId);

                    return $parentId;
                }
            }
        }

        return null;
    }

    private function addBookingItem(string $bookingItem, ?string $bookingId = null): string|bool
    {
        $requestData = ['booking_item' => $bookingItem];

        if ($bookingId) {
            $requestData['booking_id'] = $bookingId;
        }

        $responseAddItem = $this->client->post($this->url.'/api/booking/add-item', $requestData);
        if (Arr::get($responseAddItem, 'error')) {
            $this->error('Adding item failed');
        }

        $bookingId = $responseAddItem->json()['data']['booking_id'];

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
                    $age = rand(20, 60);
                    $passenger = [
                        'age' => $age,
                        'title' => 'mr',
                        'given_name' => $faker->firstName(),
                        'family_name' => $faker->lastName(),
                        'date_of_birth' => $faker->date('Y-m-d', strtotime('-'.$age.' years')),
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
                            'age' => $childAge,
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
                    'line_1' => '5047 Kessler Glens', // $faker->streetAddress(),
                    'city' => 'Ortizville', // $faker->city(),
                    'state_province_code' => 'VT', // $faker->stateAbbr(),
                    'postal_code' => 'mt', // $faker->lexify(str_repeat('?', rand(1, 7))), //$faker->postcode(),
                    'country_code' => 'US', // $faker->countryCode(),
                ],
            ],
            'special_requests' => [
                [
                    'booking_item' => $bookingItems[0],
                    'room' => 1,
                    'special_request' => 'Test Booking, please disregard.',
                ],
            ],
        ];

        if ($this->argument('scenarios') === '4') {
            $requestData['comments'] = [
                [
                    'booking_item' => $bookingItems[0],
                    'room' => 1,
                    'comment' => 'Test Comment, please disregard.',
                ],
            ];
        }

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
        $this->info('------------------------------------');
        $this->info('book: '.json_encode($response->json()));
    }

    private function cancel(string $bookingId, ?string $bookingItem = null): void
    {
        $requestData = [
            'booking_id' => $bookingId,
        ];
        if ($bookingItem) {
            $requestData['booking_item'] = $bookingItem;
        }

        $response = $this->client->delete($this->url.'/api/booking/cancel-booking', $requestData);
        $this->info('------------------------------------');
        if ($response->ok()) {
            $this->info('Cancelled booking: '.$bookingId);
        } else {
            $this->error('Failed to cancel booking: '.$bookingId);
        }
    }

    private function flowHardChange(string $bookingId, string $bookingItem, array $occupancy, string $checkin, string $checkout, ?string $roomType = null): void
    {
        $this->info('------------------------------------');
        $this->handleSleep();
        $responseAvailability = $this->availability($bookingId, $bookingItem, $occupancy, $checkin, $checkout);
        if (Arr::get($responseAvailability, 'error') || is_null(Arr::get($responseAvailability, 'success'))) {
            $this->error('Availability failed');
        }
        $this->info('softChange result : '.json_encode([
            'success' => Arr::get($responseAvailability, 'success'),
            'message' => Arr::get($responseAvailability, 'message'),
            'change_search_id' => Arr::get($responseAvailability, 'data.change_search_id'),
        ]
        ));

        $this->info('------------------------------------');
        $this->handleSleep();
        $newBookingItem = Arr::get($responseAvailability, 'data.change_search_id', false)
            ? (! $roomType
                ? $this->getBookingItem($responseAvailability)
                : $this->getBookingItemWithRoomType($responseAvailability, $roomType))
            : Uuid::uuid4()->toString();
        $this->info('$new_booking_item: '.$newBookingItem);

        $this->info('------------------------------------');
        $this->handleSleep();
        $responsePriceCheck = $this->priceCheck($bookingId, $bookingItem, $newBookingItem);
        $this->info('priceCheck: '.json_encode($responsePriceCheck));
        if (Arr::get($responsePriceCheck, 'error')) {
            $this->error('Price check failed');
        }

        $this->info('------------------------------------');
        $this->handleSleep();
        $responseHardChange = $this->hardChange($bookingId, $bookingItem, $newBookingItem, $occupancy);
        $this->info('hardChange: '.json_encode($responseHardChange));

        $this->info('------------------------------------');
        $this->handleSleep();
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
            $adults = (int) explode('-', $room)[0];
            $child = (int) explode('-', $room)[1];
            $infants = (int) (explode('-', $room)[2]);
            $passengersNumber = $adults + $child + $infants;

            for ($i = 0; $i < $passengersNumber; $i++) {
                $passengers[] = [
                    'title' => 'mr',
                    'given_name' => $faker->firstName(),
                    'family_name' => $faker->lastName(),
                    'room' => $k + 1,
                ];
            }
        }

        $params = [
            'booking_id' => $bookingId,
            'booking_item' => $bookingItem,
            'passengers' => $passengers,
        ];

        if (! empty($specialRequests)) {
            $params['special_requests'] = $specialRequests;
        }

        if (! empty($comments)) {
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

    private function hardChange(string $bookingId, string $bookingItem, string $newBookingItem, array $occupancy): array
    {
        $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy($newBookingItem);
        $rooms = $roomsBookingItem ? explode(';', $roomsBookingItem) : [];

        $passengers = [];
        $special_requests = [];
        $faker = Faker::create();

        foreach ($occupancy as $k => $occupant) {
            // Add adults
            for ($i = 0; $i < $occupant['adults']; $i++) {
                $passengers[] = [
                    'title' => 'mr',
                    'given_name' => $faker->firstName(),
                    'family_name' => $faker->lastName(),
                    'date_of_birth' => $faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'room' => $k + 1,
                ];
            }

            // Add children if any
            if (isset($occupant['children_ages'])) {
                foreach ($occupant['children_ages'] as $childAge) {
                    $passengers[] = [
                        'title' => 'ms',
                        'given_name' => 'Child',
                        'family_name' => 'Donald',
                        'date_of_birth' => date('Y-m-d', strtotime("-$childAge years")),
                        'room' => $k + 1,
                    ];
                }
            }

            // Add special requests if empty
            if (empty($special_requests)) {
                $special_requests[] = [
                    'special_request' => $faker->sentence(),
                    'room' => $k + 1,
                ];
            }
        }

        if (empty($passengers)) {
            $passengers[] = [
                'title' => 'mr',
                'given_name' => $faker->firstName(),
                'family_name' => $faker->lastName(),
                'date_of_birth' => $faker->date(),
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

    private function getBookingItemWithRoomType(array $responseData, string $roomType): ?string
    {
        $result = Arr::get($responseData, 'data.result');

        foreach ($result as $hotel) {
            $roomCombinations = Arr::get($hotel, 'room_combinations');
            $roomGroups = Arr::get($hotel, 'room_groups');

            $bookingItemRoomTypeMap = [];

            foreach ($roomGroups as $group) {
                foreach ($group['rooms'] as $room) {
                    $bookingItem = $room['booking_item'];
                    $roomTypeValue = $room['room_type'];
                    $bookingItemRoomTypeMap[$bookingItem] = $roomTypeValue;
                }
            }

            foreach ($roomCombinations as $parentId => $childIds) {
                $allMatchRoomType = true;

                foreach ($childIds as $childId) {
                    if ($bookingItemRoomTypeMap[$childId] !== $roomType) {
                        $allMatchRoomType = false;
                        break;
                    }
                }

                if ($allMatchRoomType) {
                    $this->info('Booking ITEM: '.$parentId);

                    return $parentId;
                }
            }
        }

        return null;
    }

    private function handleSleep(): void
    {
        if (! $this->isQueueSync) {
            sleep(5);
        }
    }
}
