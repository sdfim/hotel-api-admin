<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery;
use Modules\API\Suppliers\HbsiSupplier\HbsiService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use Tests\Feature\API\HotelContentRepository\TestCase;

class BookingInsuranceTest extends TestCase
{
    use SearchMockTrait;
    use WithFaker;

    private static string $searchId;
    private static ?string $bookingItem = null;
    private static string $bookingId;
    private static bool $passengersAdded = false;
    private static ?array $roomCombinations = [];

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
            'occupancy' => [['adults' => 2], ['adults' => 1]],
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

        foreach ($room_combinations as $booking_item => $room_combination) {
            if (! self::$bookingItem) {
                self::$bookingItem = $booking_item;
            }
            self::$roomCombinations[$booking_item] = $room_combination;
        }

        $this->assertNotEmpty($room_combinations);

        Mockery::close();
    }

    #[Test]
    #[Depends('test_search')]
    public function test_add_booking_item(): void
    {
        (new HbsiService())->updateBookingItemsData(self::$bookingItem, self::$roomCombinations[self::$bookingItem]);

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

        $response = $this->request()->post(route('addPassengers'), $request);

        $response->assertStatus(200);
        self::$passengersAdded = true;
    }

    #[Test]
    #[Depends('test_add_passengers')]
    public function test_add_insurance()
    {
        $request = [
            'booking_item' => self::$bookingItem,
            'insurance_provider' => 'TripMate'
        ];

        $response = $this->request()->post(route('addInsurance'), $request);

        $response->assertStatus(201);
    }

    #[Test]
    #[Depends('test_add_insurance')]
    public function test_delete_insurance()
    {
        $request = [
            'booking_item' => self::$bookingItem,
            'insurance_provider' => 'TripMate'
        ];

        $response = $this->request()->delete(route('deleteInsurance'), $request);

        $response->assertStatus(204);
    }

    private function getPassengers(): array
    {
        return [
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
}
