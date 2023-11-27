<?php

namespace Feature\API\Booking;

use Illuminate\Support\Str;
use Tests\Feature\API\Booking\HotelBookingApiTestCase;

class HotelBookingAddPassengersTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $bookingItem = $createBooking['booking_items'][0];

        $roomsCount = count($createBooking['hotel_pricing_request_data']['occupancy']);

        $addPassengersData = $this->generateAddPassengersData($roomsCount);

        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$bookingItem&booking_id=$bookingId", $addPassengersData);

        $addPassengersResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        'booking_id' => $bookingId,
                        'booking_item' => $bookingItem,
                        'status' => 'Passengers added to booking.'
                    ]
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_again_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $bookingItem = $createBooking['booking_items'][0];

        $roomsCount = count($createBooking['hotel_pricing_request_data']['occupancy']);

        $addPassengersData = $this->generateAddPassengersData($roomsCount);

        $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$bookingItem&booking_id=$bookingId", $addPassengersData);

        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$bookingItem&booking_id=$bookingId", $addPassengersData);

        $addPassengersResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        'booking_id' => $bookingId,
                        'booking_item' => $bookingItem,
                        'status' => 'Passengers updated to booking.'
                    ]
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_with_empty_booking_id_and_missed_booking_item_method_response_400(): void
    {
        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_id=");

        $addPassengersResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_with_non_existent_booking_id_and_missed_booking_item_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_id=$nonExistentBookingId");

        $addPassengersResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_with_valid_booking_item_and_missed_booking_id_method_response_400(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingItem = $createBooking['booking_items'][0];

        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$bookingItem");

        $addPassengersResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_id' => [
                        'The booking id field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_with_non_existent_booking_item_and_missed_booking_id_method_response_400(): void
    {
        $nonExistentBookingItem = Str::uuid()->toString();

        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$nonExistentBookingItem");

        $addPassengersResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_item'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_with_missed_or_empty_booking_item_and_missed_booking_id_method_response_400(): void
    {
        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=");

        $addPassengersResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_passengers_without_parameters_method_response_400(): void
    {
        $addPassengersResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers");

        $addPassengersResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type'
            ]);
    }
}
