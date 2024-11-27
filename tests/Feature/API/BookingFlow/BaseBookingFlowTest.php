<?php

namespace Tests\Feature\API\BookingFlow;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;
use Modules\API\Suppliers\HbsiSupplier\HbsiService;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\API\HotelContentRepository\TestCase;
use Illuminate\Support\Facades\Artisan;

class BaseBookingFlowTest extends TestCase
{
    use BookFlowTrait;
    use WithFaker;

    public static string $searchId;
    public static ?string $bookingItem = null;
    public static string $bookingId;
    public static bool $passengersAdded = false;
    public static int $stage = 2;
    public static ?array $roomCombinations = [];

    protected function setUp(): void
    {
        $this->useTransactions = false;
        parent::setUp();
    }

    #[Test]
    public function test_search(): void
    {
        self::$bookingItem = null;
        $this->searchMock();

        $query = [
            'type' => 'hotel',
            'destination' => 508,
            'supplier' => 'HBSI',
            'checkin' => Carbon::now()->addDays(60)->toDateString(),
            'checkout' => Carbon::now()->addDays(60 + rand(2, 5))->toDateString(),
            'occupancy' => self::$stage === 2 ? [['adults' => 2], ['adults' => 1]] : [['adults' => 1]],
        ];

        $response = $this->request()->post(route('price'), $query);

        $response->assertStatus(200);
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data')->has('success')->has('message')
        );

        $responseArray = $response->json();
        self::$searchId = Arr::get($responseArray,'data.search_id');
        $hotels = Arr::get($responseArray, 'data.results');
        $room_combinations = Arr::get($response->json(), 'data.results.0.room_combinations');

        if (self::$stage === 2) {
            foreach ($room_combinations as $booking_item => $room_combination) {
                if (! self::$bookingItem) {
                    self::$bookingItem = $booking_item;
                }
                self::$roomCombinations[$booking_item] = $room_combination;
            }
        } elseif (self::$stage === 1) {
            foreach ($hotels as $hotel) {
                foreach ($hotel['room_groups'] as $room_groups) {
                    foreach ($room_groups['rooms'] as $room) {
                        if (!$room['non_refundable']) {
                            self::$bookingItem = $room['booking_item'];
                            break 3;
                        }
                    }
                }
            }
        }

        $this->assertNotEmpty($room_combinations);

        Mockery::close();
    }

    #[Test]
    #[Depends('test_search')]
    public function test_add_booking_item(): void
    {
        if (self::$stage === 2) {
            (new HbsiService())->updateBookingItemsData(self::$bookingItem, self::$roomCombinations[self::$bookingItem]);
        }

        $response = $this->request()->post(route('addItem'), [
            'booking_item' => self::$bookingItem
        ]);

        self::$bookingId = $response['data']['booking_id'];

        $response->assertStatus(200);
    }

    #[Test]
    #[Depends('test_add_booking_item')]
    public function test_add_passengers(): void
    {
        $passengers = $this->getPassengers();
        $request = [
            'passengers' => $passengers,
            'booking_id' => self::$bookingId,
        ];

        $response = $this->request()
            ->json('POST', route('addPassengers'), $request);

        $response->assertStatus(200);
        self::$passengersAdded = true;
        self::$stage = self::$stage === 1 ? 2 : 1;
    }

    private function getPassengers(): array
    {
        if (self::$stage === 1) {
            $passengers = [
                [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName(),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'booking_items' => [
                        [
                            'booking_item' => self::$bookingItem,
                            'room' => 1,
                        ],
                    ],
                ],
            ];
        } elseif (self::$stage === 2) {
            $passengers = [
                [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName(),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->date('Y-m-d', strtotime('-'.rand(20, 60).' years')),
                    'booking_items' => [
                        [
                            'booking_item' => self::$bookingItem,
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
                            'booking_item' => self::$bookingItem,
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
                            'booking_item' => self::$bookingItem,
                            'room' => 2,
                        ],
                    ],
                ],
            ];
        }

        return $passengers;
    }

}
