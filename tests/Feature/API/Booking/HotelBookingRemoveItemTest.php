<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Support\Str;

class HotelBookingRemoveItemTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_remove_item_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $firstBookingItem = $createBooking['booking_items'][0];

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

        $bookingRemoveItemResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        'booking_id' => $bookingId,
                        'booking_item' => $firstBookingItem,
                        'status' => 'Item removed from cart.'
                    ]
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_remove_attempting_to_remove_previously_removed_item_again_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $firstBookingItem = $createBooking['booking_items'][0];

        $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

        $bookingRemoveItemResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        'booking_id' => $bookingId,
                        'booking_item' => $firstBookingItem,
                        'status' => 'This item is not in the cart'
                    ]
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_remove_item_with_non_existent_booking_id_and_booking_item_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $createBooking = $this->createHotelBooking();

        $bookingItem = $createBooking['booking_items'][0];

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$nonExistentBookingId&booking_item=$bookingItem");

        $bookingRemoveItemResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_remove_item_with_non_existent_booking_item_and_booking_id_method_response_400(): void
    {
        $nonExistentBookingItem = Str::uuid()->toString();

        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$nonExistentBookingItem");

        $bookingRemoveItemResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_item'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_remove_item_with_empty_booking_item_and_correct_booking_id_method_response_400(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=");

        $bookingRemoveItemResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_remove_item_with_missed_booking_id_and_correct_booking_item_method_response_400(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingItem = $createBooking['booking_items'][0];

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_item=$bookingItem");

        $bookingRemoveItemResponse->assertStatus(400)
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
    public function test_hotel_booking_remove_item_with_missed_booking_item_and_correct_booking_id_method_response_400(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $bookingRemoveItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId");

        $bookingRemoveItemResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_item' => [
                        'The booking item field is required.'
                    ]
                ]
            ]);
    }
}
