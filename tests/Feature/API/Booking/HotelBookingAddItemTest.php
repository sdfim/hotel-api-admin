<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Support\Str;

class HotelBookingAddItemTest extends HotelBookingApiTestCase
{
    /**
     * @test
     */
    public function test_hotel_booking_add_first_item_method_response_200(): void
    {
        $pricingSearchRequestResponse = $this->getHotelPricingSearchData();

        $bookingItems = $this->getBookingItemsFromPricingSearchResult($pricingSearchRequestResponse);

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$bookingItems[0]");

        $bookingId = $bookingAddItemResponse->json('data.booking_id');

        $bookingAddItemResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'booking_id' => $bookingId,
                ],
                'message' => 'success',
            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_item_to_an_existing_booking_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $secondBookingItem = $createBooking['booking_items'][rand(1, count($createBooking['booking_items']))];

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$secondBookingItem&booking_id=$bookingId");

        $bookingAddItemResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'booking_id' => $createBooking['booking_id'],
                ],
                'message' => 'success',
            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_previously_deleted_item_again_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];

        $firstBookingItem = $createBooking['booking_items'][0];

        $this->withHeaders($this->headers)
            ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$firstBookingItem&booking_id=$bookingId");

        $bookingAddItemResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'booking_id' => $bookingId,
                ],
                'message' => 'success',
            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_item_with_non_existent_booking_item_and_missed_booking_id_method_response_400(): void
    {
        $nonExistentBookingItem = Str::uuid()->toString();

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$nonExistentBookingItem");

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_item',
            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_item_to_non_existent_booking_id_and_correct_booking_item_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $createBooking = $this->createHotelBooking();

        $bookingItem = $createBooking['booking_items'][0];

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$bookingItem&booking_id=$nonExistentBookingId");

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id',
            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_item_with_empty_booking_item_and_missed_booking_id_method_response_400(): void
    {
        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson('api/booking/add-item?booking_item=');

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid type',
            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_item_with_empty_booking_id_and_missed_booking_item_method_response_400(): void
    {
        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson('api/booking/add-item?booking_id=');

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id',

            ]);
    }

    /**
     * @test
     */
    public function test_hotel_booking_add_item_without_parameters_method_response_400(): void
    {
        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson('api/booking/add-item');

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid type',
            ]);
    }
}
