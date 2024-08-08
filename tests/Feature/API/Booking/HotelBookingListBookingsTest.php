<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class HotelBookingListBookingsTest extends HotelBookingApiTestCase
{
    #[Test]
    public function test_hotel_booking_list_bookings_method_response_200(): void
    {
        $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $listBookingsResponse = $this->withHeaders($this->headers)
            ->getJson('api/booking/list-bookings?type=hotel&supplier=Expedia');

        $listBookingsResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'count',
                    'result' => [
                        '*' => [
                            '*' => [
                                'itinerary_id',
                                'property_id',
                                'rooms' => [
                                    '*' => [
                                        'id',
                                        'bed_group_id',
                                        'confirmation_id' => [
                                            'expedia',
                                        ],
                                        'checkin',
                                        'checkout',
                                        'number_of_adults',
                                        //                                        'child_ages' => [
                                        //                                            '*',
                                        //                                        ],
                                        'given_name',
                                        'family_name',
                                        'status',
                                        'smoking',
                                        'rate' => [
                                            'id',
                                            'merchant_of_record',
                                            'refundable',
                                            'cancel_refund' => [
                                                'amount',
                                                'currency',
                                            ],
                                            'cancel_penalties' => [
                                                '*' => [
                                                    'currency',
                                                    //                                                    'nights',
                                                    'start',
                                                    'end',
                                                ],
                                            ],
                                            'pricing' => [
                                                'nightly' => [
                                                    '*' => [
                                                        '*' => [
                                                            'value',
                                                            'type',
                                                            'currency',
                                                        ],
                                                    ],
                                                ],
                                                'totals' => [
                                                    '*' => [
                                                        '*' => [
                                                            'value',
                                                            'currency',
                                                        ],
                                                    ],
                                                ],
                                                '*' => [
                                                    '*' => [
                                                        '*' => [
                                                            'value',
                                                            'currency',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'links' => [
                                            'cancel' => [
                                                'method',
                                                'href',
                                            ],
                                            'change' => [
                                                'method',
                                                'href',
                                            ],
                                        ],
                                    ],
                                ],
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
                                'creation_date_time',
                                'affiliate_reference_id',
                                'email',
                                'phone' => [
                                    'country_code',
                                    'area_code',
                                    'number',
                                ],
                                'conversations' => [
                                    'links' => [
                                        'property' => [
                                            'method',
                                            'href',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'success',
            ]);
    }

    #[Test]
    public function test_hotel_booking_list_bookings_with_type_and_missed_supplier_method_response_400(): void
    {
        $listBookingsResponse = $this->withHeaders($this->headers)
            ->getJson('api/booking/list-bookings?type=hotel');

        $listBookingsResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'supplier' => [
                        'The supplier field is required.',
                    ],
                ],
            ]);
    }

    #[Test]
    public function test_hotel_booking_list_bookings_with_supplier_and_missed_type_method_response_400(): void
    {
        $listBookingsResponse = $this->withHeaders($this->headers)
            ->getJson('api/booking/list-bookings?supplier=Expedia');

        $listBookingsResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => [
                        'The type field is required.',
                    ],
                ],
            ]);
    }

    #[Test]
    public function test_hotel_booking_list_bookings_with_incorrect_supplier_or_type_method_response_400(): void
    {
        $supplier = Str::random();
        $type = Str::random();

        $listBookingsResponse = $this->withHeaders($this->headers)
            ->getJson("api/booking/list-bookings?type=$type&supplier=$supplier");

        $listBookingsResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'type' => [
                        'The selected type is invalid.',
                    ],
                ],
            ]);
    }

    #[Test]
    public function test_hotel_booking_list_bookings_without_parameters_method_response_400(): void
    {
        $listBookingsResponse = $this->withHeaders($this->headers)
            ->getJson('api/booking/list-bookings');

        $listBookingsResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'supplier' => [
                        'The supplier field is required.',
                    ],
                    'type' => [
                        'The type field is required.',
                    ],
                ],
            ]);
    }
}
