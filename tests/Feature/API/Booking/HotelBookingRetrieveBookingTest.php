<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Support\Str;

class HotelBookingRetrieveBookingTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_booking_method_response_200(): void
    {
        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingId = $hotelBook['booking_id'];

        $retrieveBookingResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-booking?booking_id=$bookingId");

        $retrieveBookingResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'result' => [
                        '*' => [
                            'status',
                            'booking_id',
                            'booking_item',
                            'supplier',
                            'hotel_name',
                            'rooms' => [
                                '*' => [
                                    'checkin',
                                    'checkout',
                                    'number_of_adults',
                                    'given_name',
                                    'family_name',
                                    'room_name',
                                    'room_type',
                                ],
                            ],
                            'cancellation_terms',
                            'rate',
                            'total_price',
                            'total_tax',
                            'total_fees',
                            'total_net',
                            'markup',
                            'currency',
                            'per_night_breakdown',
                            'board_basis',
                            'supplier_book_id',
                            'billing_contact' => [
                                'given_name',
                                'family_name',
                                'address' => [
                                    'line_1',
                                    'city',
                                    'state_province_code',
                                    'postal_code',
                                    'country_code',
                                ],
                            ],
                            'billing_email',
                            'billing_phone' => [
                                'country_code',
                                'area_code',
                                'number',
                            ],
                            'query' => [
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
    public function test_hotel_booking_retrieve_booking_without_parameters_method_response_400(): void
    {
        $retrieveBookingResponse = $this->withHeaders($this->headers)
            ->getJson('api/booking/retrieve-booking');

        $retrieveBookingResponse->assertStatus(400)
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
    public function test_hotel_booking_retrieve_booking_with_empty_booking_id_method_response_400(): void
    {
        $retrieveBookingResponse = $this->withHeaders($this->headers)
            ->getJson('api/booking/retrieve-booking?booking_id=');

        $retrieveBookingResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_booking_with_incorrect_booking_id_method_response_400(): void
    {
        $incorrectBookingId = Str::random(40);

        $retrieveBookingResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-booking?booking_id=$incorrectBookingId");

        $retrieveBookingResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_retrieve_booking_with_non_existent_booking_id_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid();

        $retrieveBookingResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/retrieve-booking?booking_id=$nonExistentBookingId");

        $retrieveBookingResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id'
            ]);
    }
}
