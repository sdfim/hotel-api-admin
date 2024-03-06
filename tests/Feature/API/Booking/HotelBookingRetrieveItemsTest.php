<?php

namespace Tests\Feature\API\Booking;

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

        $bookingId = $createBooking['booking_id'];

        $bookingRetrieveItemsWithoutPassengersResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=$bookingId");

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
//                                         'children',
//                                         'children_ages',
                                    ],
                                ],
                                'destination',
                            ],
                        ],
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_passengers_method_response_200(): void
    {
        $createBookingWithPassengers = $this->createHotelBookingAndAddPassengersToBookingItem();

        $bookingId = $createBookingWithPassengers['booking_id'];

        $bookingRetrieveItemsWithPassengersResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=$bookingId");

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
                                'rooms' => [
                                    '*' => [
                                        '*' => [
                                            'title',
                                            'given_name',
                                            'family_name',
                                            'date_of_birth',
                                        ],
                                    ],
                                ],
                                'search_id',
                                'booking_id',
                                'passengers' => [
                                    '*' => [
                                        'title',
                                        'given_name',
                                        'family_name',
                                        'booking_items' => [
                                            '*' => [
                                                'room',
                                                'booking_item',
                                            ],
                                        ],
                                        'date_of_birth',
                                    ],
                                ],
                                'booking_item',
                            ],
                            'request' => [
                                'type',
                                'rating',
                                'checkin',
                                'checkout',
                                'occupancy' => [
                                    '*' => [
                                        'adults',
//                                        'children_ages' => [
//                                            '*',
//                                        ],
                                    ],
                                ],
                                'destination',
                            ],
                        ],
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_missed_booking_id_method_response_400(): void
    {
        $bookingRemoveItemWithMissedBookingItemResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items");

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
    public function test_hotel_booking_retrieve_items_with_non_existent_booking_id_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $bookingRemoveItemWithMissedBookingItemResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=$nonExistentBookingId");

        $bookingRemoveItemWithMissedBookingItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_empty_booking_id_method_response_400(): void
    {
        $bookingRemoveItemWithEmptyBookingIdResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=");

        $bookingRemoveItemWithEmptyBookingIdResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id'
            ]);
    }
}
