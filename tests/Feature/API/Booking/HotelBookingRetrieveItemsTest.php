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

        $bookingRetrieveItemsResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id={$createBooking['booking_id']}");

        $bookingRetrieveItemsResponse->assertStatus(200)
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
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_passengers_method_response_200(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $bookingItem = $createBooking['booking_items'][0];

        $roomsCount = count($createBooking['hotel_pricing_request_data']['occupancy']);

        $this->addPassengersToBookingItem($bookingId, $bookingItem, $roomsCount);

        $bookingRetrieveItemsResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=$bookingId");

        $bookingRetrieveItemsResponse->assertStatus(200)
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
										'title',
                                        'given_name',
                                        'family_name',
										'date_of_birth',
                                    ],
                                ]
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
//                                        'children',
//                                        'children_ages',
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
    public function test_hotel_booking_retrieve_items_with_non_existent_booking_id_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $bookingRetrieveItemsResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=$nonExistentBookingId");

        $bookingRetrieveItemsResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_empty_booking_id_method_response_400(): void
    {
        $bookingRetrieveItemsResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items?booking_id=");

        $bookingRetrieveItemsResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_items_with_missed_booking_id_method_response_400(): void
    {
        $bookingRetrieveItemsResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-items");

        $bookingRetrieveItemsResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_id' => [
                        'The booking id field is required.'
                    ]
                ]
            ]);
    }
}
