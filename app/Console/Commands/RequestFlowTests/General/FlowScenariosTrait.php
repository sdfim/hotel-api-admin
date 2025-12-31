<?php

namespace App\Console\Commands\RequestFlowTests\General;

use App\Repositories\ApiBookingItemRepository;
use Faker\Factory as Faker;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;

trait FlowScenariosTrait
{
    protected $search_id;

    private function search(array $requestData): array
    {
        $requestData['isTestScenario'] = true;

        $response = $this->client->post($this->url.'/api/pricing/search', $requestData);

        $this->search_id = Arr::get($response->json(), 'data.search_id');
        $this->info('Search ID: '.$this->search_id);

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

    private function addBookingItem(string $bookingItem, ?string $bookingId = null): string|bool
    {
        $requestData = [
            'booking_item' => $bookingItem,
            'email_verification' => 'test@gmail.com',
            'api_client' => [
                'id' => $this->api_client_id,
            ],
        ];

        if ($bookingId) {
            $requestData['booking_id'] = $bookingId;
        }

        logger('FlowScenarios Add Booking Item Request Data: '.json_encode($requestData));

        $responseAddItem = $this->client->post($this->url.'/api/booking/add-item', $requestData);
        if (Arr::get($responseAddItem, 'error')) {
            logger('FlowScenarios Add Booking Item Response Error: '.json_encode($responseAddItem->json()));
            $this->error('Adding item failed');
        }

        if ($responseAddItem->ok() && Arr::has($responseAddItem->json(), 'data.booking_id')) {
            $bookingId = $responseAddItem->json()['data']['booking_id'];
            $this->info('Booking ID retrieved successfully: '.$bookingId);
        } else {
            logger('FlowScenarios Add Booking Item Response Failed: '.json_encode($responseAddItem->json()));
            $this->error('Failed to retrieve Booking ID. Response: '.json_encode($responseAddItem->json()));

            return false;
        }

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
            'api_client' => [
                'id' => env('TEST_API_USER_ID', 19),
            ],
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
