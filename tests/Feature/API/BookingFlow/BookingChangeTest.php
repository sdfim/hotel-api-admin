<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Mapping;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;

class BookingChangeTest extends TestCase
{
    use WithFaker, SearchMockTrait;

    private static string $bookingId;
    private static string $bookingItem;
    private static ?string $newBookingItem;

    private static bool $availableEndpointsComplete = false;

    protected function setUpTestData(): void
    {
        if (!isset(self::$user)) {
            $bookingInspector = ApiBookingInspectorRepository::getLastBooked();
            $this->assertTrue($bookingInspector != null);

            $this->runSeeders();
            $this->setAuth($bookingInspector->token_id);
            self::$bookingId = $bookingInspector->booking_id;
            self::$bookingItem = $bookingInspector->booking_item;
        } elseif (!isset(self::$bookingId)) {
            $bookingInspector = ApiBookingInspectorRepository::getLastBooked();
            $this->assertTrue($bookingInspector != null);

            self::$bookingId = $bookingInspector->booking_id;
            self::$bookingItem = $bookingInspector->booking_item;
        }
    }

    #[Test]
    public function test_available_endpoints(): void
    {
        $response = $this->request()
            ->json('GET', route('availableEndpoints'), [
                'booking_item' => self::$bookingItem,
            ]);

        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json->has('data.endpoints')->etc());
        self::$availableEndpointsComplete = true;
    }

    #[Test]
    public function test_soft_change(): void
    {
        $response = $this->request()->json(
            'PUT',
            route('changeSoftBooking'),
            $this->getSoftChangeData(),
        );

        $response->assertStatus(200);
        $response->assertJson(
            fn(AssertableJson $json) => $json
                ->where('data.status', 'Booking changed.')
                ->etc()
        );
    }

    #[Test]
    public function test_availability(): void
    {
        try {
            $apiSearchInspector = ApiSearchInspector::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('api_booking_inspector')
                ->where('booking_id', self::$bookingId)
                ->where('booking_item', self::$bookingItem)
                ->where('type', 'book')
                ->where('status', 'success')
                ->whereColumn('api_booking_inspector.search_id', 'api_search_inspector.search_id');

            })->first();
            $searchRequest = json_decode($apiSearchInspector->request, true);

            if (!Mapping::where('supplier_id','51721')->exists()) {
                $bookingItem = ApiBookingItem::where('booking_item', self::$bookingItem)->first();
                $giataId = Arr::get(json_decode($bookingItem->booking_item_data, true), 'hotel_id');
                Mapping::insert([
                    ['supplier' => 'HBSI', 'supplier_id' => '51722', 'giata_id' => 18774844, 'match_percentage' => 50],
                    ['supplier' => 'HBSI', 'supplier_id' => '51721', 'giata_id' => 42851280, 'match_percentage' => 50],
                ]);
            }

            $response = $this->request()->json(
                'POST',
                route('availabilityChange'),
                [
                    'booking_id' => self::$bookingId,
                    'booking_item' => self::$bookingItem,
                    'type' => 'hotel',
                    'destination' => 508,
                    'supplier' => 'HBSI',
                    'checkin' => $searchRequest['checkin'],
                    'checkout' => $searchRequest['checkout'],
                    'occupancy' => [['adults' => 1]],
                ],
            );

            $response->assertStatus(200);
            $response->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')->has('success')->has('message')
            );

            $room_combinations = Arr::get($response->json(), 'data.result.0.room_combinations');
            foreach ($room_combinations as $booking_item => $room_combination) {
                self::$newBookingItem = $booking_item;
                break;
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Available endpoints test not completed.');
        }

        $this->assertNotEmpty($room_combinations);
        $this->assertNotNull(self::$newBookingItem);
    }

    #[Test]
    #[Depends('test_availability')]
    public function test_price_check(): void
    {
        $response = $this->request()
            ->json('GET', route('priceCheck'), [
                'booking_id' => self::$bookingId,
                'booking_item' => self::$bookingItem,
                'new_booking_item' => self::$newBookingItem,
            ]);

        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->has('data.result.incremental_total_price')
            ->has('data.result.current_booking_item.total_price')
            ->has('data.result.new_booking_item.total_price')
            ->etc()
        );
    }

    #[Test]
    #[Depends('test_price_check')]
    public function test_hard_change(): void
    {
        $response = $this->request()->json(
            'PUT',
            route('changeHardBooking'),
            $this->getHardChangeData()
        );

        $response->assertStatus(200);
        $response->assertJson(
            fn(AssertableJson $json) => $json
                ->where('data.status', 'Booking changed.')
                ->etc()
        );
    }

    #[Test]
    #[Depends('test_hard_change')]
    public function test_retrieve_booking(): void
    {
        $response = $this->request()->json(
            'GET',
            route('retrieveBooking'),
            ['booking_id' => self::$bookingId]
        );

        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('data.result.0.status', 'booked')
            ->etc());
    }

    #[Test]
    public function test_cancel_booking(): void
    {
        $response = $this->request()->json(
            'DELETE',
            route('cancelBooking'), [
                'booking_id' => self::$bookingId,
                'booking_item' => self::$bookingItem,
                ]);

        $response->assertStatus(200);
        $response->assertJson(fn(AssertableJson $json) => $json
            ->where('data.result.0.status', 'Room canceled.')
            ->etc());
    }

    private function getPassengers(): array
    {
        $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy(self::$bookingItem);
        $rooms = explode(';', $roomsBookingItem);

        $passengers = [];
        $specialRequests = [];

        foreach ($rooms as $k => $room) {
            $adults = explode('-', $room)[0];
            for ($i = 0; $i < $adults; $i++) {
                $passengers[] = [
                    'title' => 'mr',
                    'given_name' => $this->faker->firstName,
                    'family_name' => $this->faker->lastName,
                    'date_of_birth' => $this->faker->date,
                    'room' => $k + 1,
                ];
                if (empty($specialRequests)) {
                    $specialRequests[] = [
                        'special_request' => $this->faker->sentence,
                        'room' => $k + 1,
                    ];
                }
            }
        }

        return compact('passengers', 'specialRequests');
    }

    private function getSoftChangeData(): array
    {
        return [
            'booking_id' => self::$bookingId,
            'booking_item' => self::$bookingItem,
            ...$this->getPassengers(),
        ];
    }

    private function getHardChangeData(): array
    {
        $changePassengersInspector = ApiBookingInspector::where('booking_id', self::$bookingId)
            ->where('booking_item', self::$bookingItem)
            ->where('type', 'change_passengers')->first();;
        $passengers = json_decode($changePassengersInspector->request, true)['passengers'];

        return [
            'booking_id' => self::$bookingId,
            'booking_item' => self::$bookingItem,
            'new_booking_item' => self::$newBookingItem,
            'passengers' => $passengers,
        ];
    }
}
