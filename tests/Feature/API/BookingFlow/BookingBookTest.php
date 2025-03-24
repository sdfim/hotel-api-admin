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

class BookingBookTest extends TestCase
{
    use SearchMockTrait, WithFaker;

    private static string $searchId;

    private static ?string $bookingItem = null;

    private static string $bookingId;

    private static bool $passengersAdded = false;

    private static int $stage = 2;

    private static ?array $roomCombinations = [];

    #[Test]
    public function test_search(): void
    {
        self::$bookingItem = null;
        $this->searchMock();
        $checkin = Carbon::now()->addDays(60)->toDateString();
        $checkout = Carbon::now()->addDays(60 + rand(2, 5))->toDateString();

        $occupancy = self::$stage === 2 ? [['adults' => 2], ['adults' => 1]] : [['adults' => 1]];

        $response = $this->request()
            ->json('POST', route('price'), [
                'type' => 'hotel',
                'destination' => 508,
                'supplier' => 'HBSI',
                'checkin' => $checkin,
                'checkout' => $checkout,
                'occupancy' => $occupancy,
            ]);

        $response->assertStatus(200);
        $response->assertJson(
            fn (AssertableJson $json) => $json->has('data')->has('success')->has('message')
        );

        $responseArray = $response->json();
        self::$searchId = Arr::get($responseArray, 'data.search_id');
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
                        if (! $room['non_refundable']) {
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
            (new HbsiService)->updateBookingItemsData(self::$bookingItem, false, self::$roomCombinations[self::$bookingItem]);
        }

        $response = $this->request()->json('POST', route('addItem'), [
            'booking_item' => self::$bookingItem,
        ]);

        if (! isset($response['data'])) {
            $this->markTestSkipped('Booking ID not found in the API response.');
        }

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
        self::$stage--;
    }

    #[Test]
    #[Depends('test_add_passengers')]
    public function test_book()
    {
        $response = $this->request()->json('POST', route('book'),
            $this->requestBookData()
        );

        $response->assertStatus(200);
    }

    #[Test]
    #[Depends('test_book')]
    public function test_cancel()
    {
        $response = $this->request()->json('DELETE', route('cancelBooking'), [
            'booking_id' => self::$bookingId,
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    #[Depends('test_cancel')]
    public function test_search_again(): void
    {
        $this->test_search();
    }

    #[Test]
    #[Depends('test_search_again')]
    public function test_add_booking_item_again(): void
    {
        $this->test_add_booking_item();
    }

    #[Test]
    #[Depends('test_add_booking_item_again')]
    public function test_add_passengers_again(): void
    {
        $this->test_add_passengers();
    }

    #[Test]
    #[Depends('test_add_passengers_again')]
    public function test_book_again()
    {
        $this->test_book();
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

    private function requestBookData(): array
    {
        return [
            'booking_id' => self::$bookingId,
            'amount_pay' => 'Deposit',
            'booking_contact' => [
                'first_name' => 'Test',
                'last_name' => 'Test',
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
                    'booking_item' => self::$bookingItem,
                    'room' => 1,
                    'special_request' => 'UJV Test Booking, please disregard.',
                ],
            ],
            'credit_cards' => [
                [
                    'credit_card' => [
                        'cvv' => '123',
                        'number' => 4001919257537193,
                        'card_type' => 'VISA',
                        'name_card' => 'Visa',
                        'expiry_date' => '09/2026',
                        'billing_address' => null,
                    ],
                    'booking_item' => self::$bookingItem,
                ],
            ],
        ];
    }
}
