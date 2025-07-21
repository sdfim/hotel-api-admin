<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Mapping;
use App\Repositories\ApiBookingItemRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

uses(WithFaker::class)
    ->beforeEach(function () {
        if (! isset(test()->bookingId)) {
            test()->stage = 2;
            test()->search();
            test()->add_booking_item();
            test()->add_passengers();
        }
        test()->newBookingItem = null;
        test()->availableEndpointsComplete = false;
    });

test('available endpoints', function () {
    $response = test()->request()
        ->json('GET', route('availableEndpoints'), ['booking_item' => test()->bookingItem]);
    $response->assertStatus(200);
    $response->assertJson(fn (AssertableJson $json) => $json->has('data.endpoints')->etc());
    test()->availableEndpointsComplete = true;
});

test('soft change', function () {
    $response = test()->request()
        ->json('PUT', route('changeSoftBooking'), test()->getSoftChangeData());
    $response->assertStatus(200);
    $response->assertJson(
        fn (AssertableJson $json) => $json
            ->where('data.status', 'Booking changed.')
            ->etc()
    );
});

test('availability', function () {
    $apiSearchInspector = ApiSearchInspector::whereExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('api_booking_inspector')
            ->where('booking_id', test()->bookingId)
            ->where('booking_item', test()->bookingItem)
            ->where('type', 'book')
            ->where('status', 'success')
            ->whereColumn('api_booking_inspector.search_id', 'api_search_inspector.search_id');

    })->first();
    $searchRequest = json_decode($apiSearchInspector->request, true);

    if (! Mapping::where('supplier_id', '51721')->exists()) {
        $bookingItem = ApiBookingItem::where('booking_item', test()->bookingItem)->first();
        $giataId = Arr::get(json_decode($bookingItem->booking_item_data, true), 'hotel_id');
        Mapping::insert([
            ['supplier' => 'HBSI', 'supplier_id' => '51722', 'giata_id' => 18774844, 'match_percentage' => 50],
            ['supplier' => 'HBSI', 'supplier_id' => '51721', 'giata_id' => 42851280, 'match_percentage' => 50],
        ]);
    }

    $response = test()->request()
        ->json('POST', route('availabilityChange'), [
            'booking_id' => test()->bookingId,
            'booking_item' => test()->bookingItem,
            'type' => 'hotel',
            'destination' => 508,
            'supplier' => 'HBSI',
            'checkin' => $searchRequest['checkin'],
            'checkout' => $searchRequest['checkout'],
            'occupancy' => [['adults' => 1]],
        ]);

    $response->assertStatus(200);
    $response->assertJson(
        fn (AssertableJson $json) => $json->has('data')->has('success')->has('message')
    );

    $room_combinations = Arr::get($response->json(), 'data.result.0.room_combinations');
    foreach ($room_combinations as $booking_item => $room_combination) {
        test()->newBookingItem = $booking_item;
        break;
    }

    test()->assertNotEmpty($room_combinations);
    test()->assertNotNull(test()->newBookingItem);
});

test('price check', function () {
    $response = test()->request()
        ->json('GET', route('priceCheck'), [
            'booking_id' => test()->bookingId,
            'booking_item' => test()->bookingItem,
            'new_booking_item' => test()->newBookingItem,
        ]);
    $response->assertStatus(200);
    $response->assertJson(fn (AssertableJson $json) => $json
        ->has('data.result.incremental_total_price')
        ->has('data.result.current_booking_item.total_price')
        ->has('data.result.new_booking_item.total_price')
        ->etc()
    );
})->depends('availability');

test('hard change', function () {
    $response = test()->request()
        ->json('PUT', route('changeHardBooking'), test()->getHardChangeData());
    $response->assertStatus(200);
    $response->assertJson(
        fn (AssertableJson $json) => $json
            ->where('data.status', 'Booking changed.')
            ->etc()
    );
})->depends('price check');

test('retrieve booking', function () {
    $response = test()->request()
        ->json('GET', route('retrieveBooking'), ['booking_id' => test()->bookingId]);
    $response->assertStatus(200);
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('data.result.0.status', 'booked')
        ->etc());
});

test('cancel booking', function () {
    $response = test()->request()
        ->json('DELETE', route('cancelBooking'), [
            'booking_id' => test()->bookingId,
            'booking_item' => test()->bookingItem,
        ]);
    $response->assertStatus(200);
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('data.result.0.status', 'Room canceled.')
        ->etc());
});

function getPassengers(): array
{
    $roomsBookingItem = ApiBookingItemRepository::getRateOccupancy(test()->bookingItem);
    $rooms = explode(';', $roomsBookingItem);

    $passengers = [];
    $specialRequests = [];

    foreach ($rooms as $k => $room) {
        $adults = explode('-', $room)[0];
        for ($i = 0; $i < $adults; $i++) {
            $passengers[] = [
                'title' => 'mr',
                'given_name' => test()->faker->firstName(),
                'family_name' => test()->faker->lastName(),
                'date_of_birth' => test()->faker->date(),
                'room' => $k + 1,
            ];
            if (empty($specialRequests)) {
                $specialRequests[] = [
                    'special_request' => test()->faker->sentence(),
                    'room' => $k + 1,
                ];
            }
        }
    }

    return compact('passengers', 'specialRequests');
}

function getSoftChangeData(): array
{
    return [
        'booking_id' => test()->bookingId,
        'booking_item' => test()->bookingItem,
        ...test()->getPassengers(),
    ];
}

function getHardChangeData(): array
{
    $changePassengersInspector = ApiBookingInspector::where('booking_id', test()->bookingId)
        ->where('booking_item', test()->bookingItem)
        ->where('type', 'change_passengers')->first();
    $passengers = json_decode($changePassengersInspector->request, true)['passengers'];

    return [
        'booking_id' => test()->bookingId,
        'booking_item' => test()->bookingItem,
        'new_booking_item' => test()->newBookingItem,
        'passengers' => $passengers,
    ];
}