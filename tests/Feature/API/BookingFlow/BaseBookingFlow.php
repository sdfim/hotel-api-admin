<?php

namespace Tests\Feature\API\BookingFlow;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;
use Modules\API\Services\HotelCombinationService;
use Modules\Enums\SupplierNameEnum;

trait BaseBookingFlow
{
    use BookFlowTrait;
    use WithFaker;

    public string $searchId;

    public ?string $bookingItem = null;

    public string $bookingId;

    public bool $passengersAdded = false;

    // 0 - 1 room, 1 - 1 room refundable, 2 - 2 rooms
    public int $stage = 2;

    public ?array $roomCombinations = [];

    public function search(): void
    {
        $this->bookingItem = null;
        $this->searchMock();
        $diffDays = rand(20, 40);

        $response = $this->request()->post(route('price'), [
            'type' => 'hotel',
            'destination' => 508,
            'supplier' => 'HBSI',
            'checkin' => Carbon::now()->addDays($diffDays)->toDateString(),
            'checkout' => Carbon::now()->addDays($diffDays + rand(2, 5))->toDateString(),
            'occupancy' => $this->stage === 2 ? [['adults' => 2], ['adults' => 1]] : [['adults' => 1]],
        ]);

        $response->assertStatus(200);
        $response->assertJson(
            fn (AssertableJson $json) => $json->has('data')->has('success')->has('message')
        );

        $responseArray = $response->json();
        $this->searchId = Arr::get($responseArray, 'data.search_id');
        $hotels = Arr::get($responseArray, 'data.results');
        $room_combinations = Arr::get($response->json(), 'data.results.0.room_combinations');

        if ($this->stage === 2 || $this->stage === 0) {
            foreach ($room_combinations as $booking_item => $room_combination) {
                if (! $this->bookingItem) {
                    $this->bookingItem = $booking_item;
                }
                $this->roomCombinations[$booking_item] = $room_combination;
            }
        } elseif ($this->stage === 1) {
            foreach ($hotels as $hotel) {
                foreach ($hotel['room_groups'] as $room_groups) {
                    foreach ($room_groups['rooms'] as $room) {
                        if (! $room['non_refundable']) {
                            $this->bookingItem = $room['booking_item'];
                            break 3;
                        }
                    }
                }
            }
        }

        $this->assertNotEmpty($room_combinations);

        Mockery::close();
    }

    public function add_booking_item(): void
    {
        if ($this->stage === 2) {
            (new HotelCombinationService(SupplierNameEnum::HBSI->value))
                ->updateBookingItemsData($this->bookingItem, $this->roomCombinations[$this->bookingItem]);
        }

        $response = $this->request()->post(route('addItem'), [
            'booking_item' => $this->bookingItem,
        ]);

        $this->bookingId = $response['data']['booking_id'];

        $response->assertStatus(200);
    }

    public function add_passengers(): void
    {
        $response = $this->request()->post(route('addPassengers'), [
            'passengers' => $this->getPassengers(),
            'booking_id' => $this->bookingId,
        ]);

        $response->assertStatus(200);
        $this->passengersAdded = true;
    }

    private function getPassengers(): array
    {
        if ($this->stage === 1 || $this->stage === 0) {
            $passengers = [
                [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName(),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'booking_items' => [
                        [
                            'booking_item' => $this->bookingItem,
                            'room' => 1,
                        ],
                    ],
                ],
            ];
        } elseif ($this->stage === 2) {
            $passengers = [
                [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName(),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'booking_items' => [
                        [
                            'booking_item' => $this->bookingItem,
                            'room' => 1,
                        ],
                    ],
                ],
                [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName(),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'booking_items' => [
                        [
                            'booking_item' => $this->bookingItem,
                            'room' => 1,
                        ],
                    ],
                ],
                [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName(),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'booking_items' => [
                        [
                            'booking_item' => $this->bookingItem,
                            'room' => 2,
                        ],
                    ],
                ],
            ];
        }

        return $passengers;
    }
}
