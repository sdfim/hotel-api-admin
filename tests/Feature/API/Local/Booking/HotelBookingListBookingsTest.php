<?php

use Illuminate\Support\Str;

test('hotel booking list bookings method response 200', function () {
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
});

test('hotel booking list bookings with type and missed supplier method response 400', function () {
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
});

test('hotel booking list bookings with supplier and missed type method response 400', function () {
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
});

test('hotel booking list bookings with incorrect supplier or type method response 400', function () {
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
});

test('hotel booking list bookings without parameters method response 400', function () {
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
});