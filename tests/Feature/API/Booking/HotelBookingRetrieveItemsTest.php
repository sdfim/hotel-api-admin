<?php

namespace Feature\API\Booking;

use Illuminate\Support\Str;

class HotelBookingRetrieveItemsTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_without_passengers_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingRetrieveItemsWithoutPassengersResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id={$createBooking['booking_id']}");

        $bookingRetrieveItemsWithoutPassengersResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'result' => [
                        '*' => [
                            'booking_id',
                            'booking_item',
                            'search_id',
                            'supplier',
                            'supplier_data' => [
                                'rate',
                                'room_id',
                                'hotel_id',
                                'bed_groups',
                            ],
                            'pricing_data' => [
                                'currency',
                                'total_net',
                                'total_tax',
                                'total_fees',
                                'total_price',
                                'booking_item',
                                'giata_room_code',
                                'giata_room_name',
                                'supplier_room_name',
                                'per_day_rate_breakdown',
                                'affiliate_service_charge',
                            ],
                            'passengers',
                            'request' => [
                                'type',
                                'rating',
                                'checkin',
                                'checkout',
                                'currency',
                                'supplier',
                                'occupancy' => [
                                    '*' => [
                                        'adults',
                                        'children',
                                        'children_ages',
                                    ],
                                ],
                                'destination',
                            ],
                        ],
                    ],
                ],
                'message',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_passengers_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        //TODO: add call of add-passengers

        $bookingRetrieveItemsWithPassengersResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id={$createBooking['booking_id']}");

        $bookingRetrieveItemsWithPassengersResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'result' => [
                        '*' => [
                            'booking_id',
                            'booking_item',
                            'search_id',
                            'supplier',
                            'supplier_data' => [
                                'rate',
                                'room_id',
                                'hotel_id',
                                'bed_groups',
                            ],
                            'pricing_data' => [
                                'currency',
                                'total_net',
                                'total_tax',
                                'total_fees',
                                'total_price',
                                'booking_item',
                                'giata_room_code',
                                'giata_room_name',
                                'supplier_room_name',
                                'per_day_rate_breakdown',
                                'affiliate_service_charge',
                            ],
                            'passengers' => [
                                '*' => [
                                    'given_name',
                                    'family_name',
                                ],
                                'title',
                                'last_name',
                                'search_id',
                                'booking_id',
                                'first_name',
                                'booking_item',
                            ],
                            'request' => [
                                'type',
                                'rating',
                                'checkin',
                                'checkout',
                                'currency',
                                'supplier',
                                'occupancy' => [
                                    '*' => [
                                        'adults',
                                        'children',
                                        'children_ages',
                                    ],
                                ],
                                'destination',
                            ],
                        ],
                    ],
                ],
                'message',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_missed_booking_id_method_response_400(): void
    {
        $bookingRemoveItemWithMissedBookingItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/retrieve-items");

        $bookingRemoveItemWithMissedBookingItemResponse->assertStatus(400)
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
    public function test_hotel_booking_retrieve_items_with_empty_booking_id_method_response_400(): void
    {
        $bookingRemoveItemWithEmptyBookingIdResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/retrieve-items?booking_id=");

        $bookingRemoveItemWithEmptyBookingIdResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }
}
