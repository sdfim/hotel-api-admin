<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Support\Str;

class HotelBookingAddItemTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
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
                    'booking_id' => $bookingId
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_item_to_an_existing_booking_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $secondBookingItem = $createBooking['booking_items'][1] ?? $createBooking['booking_items'][0];

        $bookingAddItemToAnExistingBooking = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$secondBookingItem&booking_id={$createBooking['booking_id']}");

        $bookingAddItemToAnExistingBooking->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'booking_id' => $createBooking['booking_id']
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_previously_deleted_item_again_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $firstBookingItem = $createBooking['booking_items'][0];

        $this->withHeaders($this->headers)
            ->postJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

        $bookingAddPreviouslyDeletedItemAgainResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$firstBookingItem&booking_id=$bookingId");

        $bookingAddPreviouslyDeletedItemAgainResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'booking_id' => $bookingId
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_item_with_non_existent_booking_item_method_response_400(): void
    {
        $nonExistentBookingItem = Str::uuid()->toString();

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$nonExistentBookingItem");

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_item'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_item_to_non_existent_booking_id_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $createBooking = $this->createHotelBooking();

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item={$createBooking['booking_items'][0]}&booking_id=$nonExistentBookingId");

        $bookingAddItemResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_add_item_with_missed_or_empty_booking_item_method_response_400(): void
    {
        $bookingRemoveItemWithMissedBookingItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_id=");

        $bookingRemoveItemWithMissedBookingItemResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type'
            ]);
    }
}
