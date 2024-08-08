<?php

namespace Tests\Feature\API\BookingFlow;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;

class BookingBookTest extends TestCase
{
    use WithFaker, SearchMockTrait;

    private static string $searchId;
    private static string $bookingItem;
    private static string $bookingId;
    private static bool $passengersAdded = false;

    #[Test]
    public function test_search(): void
    {
        $this->searchMock();
        $checkin = Carbon::now()->addDays(150)->toDateString();
        $checkout = Carbon::now()->addDays(150 + rand(2, 5))->toDateString();

        $response = $this->request()
            ->json('POST', route('price'), [
                'type' => 'hotel',
                'destination' => 508,
                'supplier' => 'HBSI',
                'checkin' => $checkin,
                'checkout' => $checkout,
                'occupancy' => [['adults' => 1]],
            ]);

        $response->assertStatus(200);
        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data')->has('success')->has('message')
        );

        $responseArray = $response->json();
        self::$searchId = Arr::get($responseArray,'data.search_id');
        $hotels = Arr::get($responseArray, 'data.results');
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

        Mockery::close();
    }

    #[Test]
    #[Depends('test_search')]
    public function test_add_booking_item(): void
    {
        $response = $this->request()
            ->json(
                'POST',
                route('addItem'),
                ['booking_item' => self::$bookingItem]
            );
        self::$bookingId = $response['data']['booking_id'];

        $response->assertStatus(200);
    }

    #[Test]
    #[Depends('test_add_booking_item')]
    public function test_add_passengers(): void
    {
        $request = [
            'passengers' => [
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
            ],
            'booking_id' => self::$bookingId,
        ];

        $response = $this->request()
            ->json('POST', route('addPassengers'), $request);

        $response->assertStatus(200);
        self::$passengersAdded = true;
    }

    #[Test]
    #[Depends('test_add_passengers')]
    public function test_book()
    {
        $response = $this->request()
            ->json(
                'POST',
                route('book'),
                $this->requestBookData()
            );

        $response->assertStatus(200);
    }

    private function requestBookData(): array
    {
        return [
            'booking_id' => self::$bookingId,
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
                ]
            ]
        ];
    }
}
