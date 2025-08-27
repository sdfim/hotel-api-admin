<?php

namespace App\Console\Commands\RequestFlowTests\HBSI;

use App\Repositories\ApiBookingItemRepository;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;

trait FlowHbsiScenariosTrait
{
    public function findBookingItemByRoomParams(array $searchResponse, array $allRoomParamsArray): ?string
    {
        // Remove 'special_request' and 'comment' from each room params
        $roomParamsArray = array_map(function ($params) {
            unset($params['special_request'], $params['comment']);

            return $params;
        }, $allRoomParamsArray);

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
                        'rate_plan_code' => $room['rate_plan_code'] ?? null,
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

    public function processBooking(array $occupancy, string $checkin, string $checkout, array $roomParamsArray = [], ?string $inputBookingId = null): array|bool
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
        $responseBook = $this->book($bookingId, [$bookingItem], $roomParamsArray);
        if (Arr::get($responseBook, 'error')) {
            $this->error('Booking failed');
            exit(1);
        }
        sleep(5);

        return [$bookingId, $bookingItem];
    }

    public function search(array $occupancy, string $checkin, string $checkout): array
    {
        $faker = Faker::create();

        $requestData = [
            'type' => 'hotel',
            'supplier' => $this->supplier,
            'giata_ids' => [$this->giata_id],
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => $occupancy,
            'results_per_page' => 100,
            'blueprint_exists' => false,
        ];

        $response = $this->client->post($this->url.'/api/pricing/search', $requestData);

        $searchId = Arr::get($response->json(), 'data.search_id');
        $this->info('Search ID: '.$searchId);

        return $response->json();
    }

    public function fetchBookingItem(array $searchResponse): ?string
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

    public function addBookingItem(string $bookingItem, ?string $bookingId = null): string|bool
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

    public function addPassengers(string $bookingId, array $bookingItems, array $occupancy): void
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

    public function retrieveItems(string $bookingId): void
    {
        $requestData = [
            'booking_id' => $bookingId,
        ];

        $response = $this->client->get($this->url.'/api/booking/retrieve-items', $requestData);
        $this->info('retrieveItems: '.json_encode($response->json()));
    }

    public function book(string $bookingId, array $bookingItems, array $roomParamsArray = []): void
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
                    'line_1' => '5047 Kessler Glens',
                    'city' => 'Ortizville',
                    'state_province_code' => 'VT',
                    'postal_code' => 'mt',
                    'country_code' => 'US',
                ],
            ],
        ];

        // Add special_requests and comments from roomParamsArray if present
        if (isset($roomParamsArray[0]['special_requests'])) {
            $requestData['special_requests'][0] = [
                'booking_item' => $bookingItems[0],
                'room' => 1,
                'comment' => $roomParamsArray[0]['special_request'],
            ];
        }
        if (isset($roomParamsArray[0]['comments'])) {
            $requestData['comments'][0] = [
                'booking_item' => $bookingItems[0],
                'room' => 1,
                'comment' => $roomParamsArray[0]['comment'],
            ];
        }

        // Credit cards
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

        //        if ($this->argument('scenarios') === '4') {
        //            $requestData['comments'] = [
        //                [
        //                    'booking_item' => $bookingItems[0],
        //                    'room' => 1,
        //                    'comment' => 'Test Comment, please disregard.',
        //                ],
        //            ];
        //        }

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

    public function cancel(string $bookingId, ?string $bookingItem = null): void
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

    public function flowHardChange(string $bookingId, string $bookingItem, array $occupancy, string $checkin, string $checkout, ?string $roomType = null): void
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

    public function availableEndpoints($bookingItem)
    {
        $response = $this->client->get($this->url.'/api/booking/change/available-endpoints/?booking_item='.$bookingItem);

        $listEndpoints = $response->json()['data']['endpoints'];

        return $listEndpoints;
    }

    public function softChange(string $bookingId, string $bookingItem, array $specialRequests = [], array $comments = []): array
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

    public function availability(string $bookingId, string $bookingItem, array $occupancy, string $checkin, string $checkout): array
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

    public function priceCheck(string $bookingId, string $bookingItem, string $newBookingItem)
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

    public function hardChange(string $bookingId, string $bookingItem, string $newBookingItem, array $occupancy): array
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

    public function retrieveBooking(string $bookingId): ?array
    {
        $params = [
            'booking_id' => $bookingId,
        ];

        $response = $this->client->get($this->url.'/api/booking/retrieve-booking', $params);

        return $response->json();
    }

    public function getBookingItem(array $responseData): ?string
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

    public function getBookingItemWithRoomType(array $responseData, string $roomType): ?string
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

    public function handleSleep(): void
    {
        if (! $this->isQueueSync) {
            sleep(10);
        }
    }
}
