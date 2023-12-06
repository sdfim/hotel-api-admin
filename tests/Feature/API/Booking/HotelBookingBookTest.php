<?php

namespace Feature\API\Booking;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature\API\Booking\HotelBookingApiTestCase;
use Faker\Provider\en_UG\Address;

class HotelBookingBookTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_method_response_200(): void
    {
        $bookingId = $this->createHotelBookingAndAddPassengersToBookingItem();

        $hotelBookData = $this->generateHotelBookData();

        $bookResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/book?booking_id=$bookingId", $hotelBookData);

        $bookResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'status',
                        'booking_id',
                        'booking_item',
                        'supplier',
                        'hotel_name',
                        'rooms' => [
                            'room_name',
                            'meal_plan',
                        ],
                        'cancellation_terms',
                        'rate',
                        'total_price',
                        'total_tax',
                        'total_fees',
                        'total_net',
                        'affiliate_service_charge',
                        'currency',
                        'per_night_breakdown',
                        'links' => [
                            'remove' => [
                                'method',
                                'href',
                            ],
                            'change' => [
                                'method',
                                'href',
                            ],
                            'retrieve' => [
                                'method',
                                'href',
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
    public function test_hotel_booking_book_without_add_passengers_to_booking_item_method_response_400(): void
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $bookingItem = $createBooking['booking_items'][0];

        $hotelBookData = $this->generateHotelBookData();

        $bookResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/book?booking_id=$bookingId", $hotelBookData);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => [
                    'error' => 'Passengers not found.',
                    'booking_item' => $bookingItem
                ],
                'message' => 'success'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_without_booking_id_method_response_400(): void
    {
        $bookResponse = $this->withHeaders($this->headers)
            ->postJson('api/booking/book');

        $bookResponse->assertStatus(400)
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
    public function test_hotel_booking_book_with_non_existent_booking_id_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $bookResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/book?booking_id=$nonExistentBookingId");

        $bookResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_booking_id_method_response_400(): void
    {
        $bookResponse = $this->withHeaders($this->headers)
            ->postJson('api/booking/book?booking_id=');

        $bookResponse->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid booking_id'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_amount_pay_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_amount_pay']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'amount_pay' => [
                        'The selected amount pay is invalid.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_amount_pay_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_amount_pay']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'amount_pay' => [
                        'The amount pay field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_amount_pay_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['empty_amount_pay']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'amount_pay' => [
                        'The amount pay field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.first_name' => [
                        'The booking contact.first name field is required.'
                    ],
                    'booking_contact.last_name' => [
                        'The booking contact.last name field is required.'
                    ],
                    'booking_contact.email' => [
                        'The booking contact.email field is required.'
                    ],
                    'booking_contact.phone.country_code' => [
                        'The booking contact.phone.country code field is required.'
                    ],
                    'booking_contact.phone.area_code' => [
                        'The booking contact.phone.area code field is required.'
                    ],
                    'booking_contact.phone.number' => [
                        'The booking contact.phone.number field is required.'
                    ],
                    'booking_contact.address.line_1' => [
                        'The booking contact.address.line 1 field is required.'
                    ],
                    'booking_contact.address.city' => [
                        'The booking contact.address.city field is required.'
                    ],
                    'booking_contact.address.state_province_code' => [
                        'The booking contact.address.state province code field is required.'
                    ],
                    'booking_contact.address.postal_code' => [
                        'The booking contact.address.postal code field is required.'
                    ],
                    'booking_contact.address.country_code' => [
                        'The booking contact.address.country code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_first_name_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_first_name']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.first_name' => [
                        'The booking contact.first name field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_booking_contact_first_name_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['empty_booking_contact_first_name']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.first_name' => [
                        'The booking contact.first name field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_last_name_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_last_name']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.last_name' => [
                        'The booking contact.last name field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_booking_contact_last_name_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['empty_booking_contact_last_name']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.last_name' => [
                        'The booking contact.last name field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_email_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_email']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.email' => [
                        'The booking contact.email field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_booking_contact_email_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['empty_booking_contact_email']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.email' => [
                        'The booking contact.email field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_email_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_email']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.email' => [
                        'The booking contact.email must be a valid email address.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_phone_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_phone']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.country_code' => [
                        'The booking contact.phone.country code field is required.'
                    ],
                    'booking_contact.phone.area_code' => [
                        'The booking contact.phone.area code field is required.'
                    ],
                    'booking_contact.phone.number' => [
                        'The booking contact.phone.number field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_phone_country_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_phone_country_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.country_code' => [
                        'The booking contact.phone.country code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_booking_contact_phone_country_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['empty_booking_contact_phone_country_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.country_code' => [
                        'The booking contact.phone.country code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_phone_country_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_phone_country_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.country_code' => [
                        'The booking contact.phone.country code must be an integer.',
                        'The selected booking contact.phone.country code is invalid.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_type_booking_contact_phone_country_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_type_booking_contact_phone_country_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.country_code' => [
                        'The booking contact.phone.country code must be an integer.',
                        'The selected booking contact.phone.country code is invalid.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_phone_area_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_phone_area_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.area_code' => [
                        'The booking contact.phone.area code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_phone_area_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_phone_area_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.area_code' => [
                        'The booking contact.phone.area code must be 3 digits.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_type_booking_contact_phone_area_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_type_booking_contact_phone_area_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.area_code' => [
                        'The booking contact.phone.area code must be an integer.',
                        'The booking contact.phone.area code must be 3 digits.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_phone_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_phone_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.number' => [
                        'The booking contact.phone.number field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_empty_booking_contact_phone_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['empty_booking_contact_phone_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.number' => [
                        'The booking contact.phone.number field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_phone_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_phone_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.number' => [
                        'The booking contact.phone.number must be between 4 and 8 digits.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_type_booking_contact_phone_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_type_booking_contact_phone_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.phone.number' => [
                        'The booking contact.phone.number must be a number.',
                        'The booking contact.phone.number must be between 4 and 8 digits.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_address_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_address']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.city' => [
                        'The booking contact.address.city field is required.'
                    ],
                    'booking_contact.address.state_province_code' => [
                        'The booking contact.address.state province code field is required.'
                    ],
                    'booking_contact.address.postal_code' => [
                        'The booking contact.address.postal code field is required.'
                    ],
                    'booking_contact.address.country_code' => [
                        'The booking contact.address.country code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_address_line_1_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_address_line_1']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.line_1' => [
                        'The booking contact.address.line 1 field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_address_line_1_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_address_line_1']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.line_1' => [
                        'The booking contact.address.line 1 must not be greater than 255 characters.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_address_city_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_address_city']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.city' => [
                        'The booking contact.address.city field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_address_city_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_address_city']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.city' => [
                        'The booking contact.address.city must not be greater than 100 characters.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_address_state_province_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_address_state_province_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_address_state_province_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_address_state_province_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.state_province_code' => [
                        'The booking contact.address.state province code format is invalid.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_address_postal_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_address_postal_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.postal_code' => [
                        'The booking contact.address.postal code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_address_postal_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_address_postal_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.postal_code' => [
                        'The booking contact.address.postal code must be between 1 and 7 characters.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_booking_contact_address_country_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_booking_contact_address_country_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.country_code' => [
                        'The booking contact.address.country code field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_booking_contact_address_country_code_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_booking_contact_address_country_code']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_contact.address.country_code' => [
                        'The selected booking contact.address.country code is invalid.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_credit_card_name_card_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_credit_card_name_card']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'credit_card.name_card' => [
                        'The credit card.name card field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_credit_card_name_card_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_credit_card_name_card']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'credit_card.name_card' => [
                        'The credit card.name card must be between 2 and 255 characters.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_credit_card_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_credit_card_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'credit_card.number' => [
                        'The credit card.number field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_credit_card_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_credit_card_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'credit_card.number' => [
                        'The credit card.number must be between 13 and 19 digits.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_type_credit_card_number_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_type_credit_card_number']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'credit_card.number' => [
                        'The credit card.number must be an integer.',
                        'The credit card.number must be between 13 and 19 digits.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_credit_card_card_type_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_credit_card_card_type']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_credit_card_card_type_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_credit_card_card_type']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_credit_card_expiry_date_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_credit_card_expiry_date']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_credit_card_expiry_date_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_credit_card_expiry_date']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_past_date_credit_card_expiry_date_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['past_date_credit_card_expiry_date']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_missed_credit_card_cvv_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['missed_credit_card_cvv']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_credit_card_cvv_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_credit_card_cvv']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_book_with_incorrect_type_credit_card_cvv_method_response_400(): void
    {
        $bookResponse = $this->sendBookRequestWithIncorrectData(['incorrect_type_credit_card_cvv']);

        $bookResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @param array $keysToFail
     * @return TestResponse
     */
    protected function sendBookRequestWithIncorrectData(array $keysToFail = []): TestResponse
    {
        $bookingId = $this->createHotelBookingAndAddPassengersToBookingItem();

        $wrongHotelBookData = $this->hotelBookData($keysToFail);

        return $this->withHeaders($this->headers)
            ->postJson("api/booking/book?booking_id=$bookingId", $wrongHotelBookData);
    }

    /**
     * @param array $keysToFail An array of keys indicating which values to modify or remove.
     * Possible values:
     * @return array The hotel search request data.
     */
    private function hotelBookData(array $keysToFail = []): array
    {
        $data = $this->generateHotelBookData();

        if (count($keysToFail) > 0) {
            if (in_array('incorrect_amount_pay', $keysToFail)) $data['amount_pay'] = $this->faker->text(10);
            if (in_array('missed_amount_pay', $keysToFail)) unset($data['amount_pay']);
            if (in_array('empty_amount_pay', $keysToFail)) $data['amount_pay'] = '';
            if (in_array('missed_booking_contact', $keysToFail)) unset($data['booking_contact']);
            if (in_array('missed_booking_contact_first_name', $keysToFail)) unset($data['booking_contact']['first_name']);
            if (in_array('empty_booking_contact_first_name', $keysToFail))
                $data['booking_contact']['first_name'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_last_name', $keysToFail)) unset($data['booking_contact']['last_name']);
            if (in_array('empty_booking_contact_last_name', $keysToFail))
                $data['booking_contact']['last_name'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_email', $keysToFail)) unset($data['booking_contact']['email']);
            if (in_array('empty_booking_contact_email', $keysToFail)) $data['booking_contact']['email'] = '';
            if (in_array('incorrect_booking_contact_email', $keysToFail))
                $data['booking_contact']['email'] = $this->faker->text(10);
            if (in_array('missed_booking_contact_phone', $keysToFail)) unset($data['booking_contact']['phone']);
            if (in_array('missed_booking_contact_phone_country_code', $keysToFail))
                unset($data['booking_contact']['phone']['country_code']);
            if (in_array('empty_booking_contact_phone_country_code', $keysToFail))
                unset($data['booking_contact']['phone']['country_code']);
            if (in_array('incorrect_booking_contact_phone_country_code', $keysToFail))
                $data['booking_contact']['phone']['country_code'] = $this->faker->randomNumber(4, true);
            if (in_array('incorrect_type_booking_contact_phone_country_code', $keysToFail))
                $data['booking_contact']['phone']['country_code'] = $this->faker->text(4);
            if (in_array('missed_booking_contact_phone_area_code', $keysToFail))
                unset($data['booking_contact']['phone']['area_code']);
            if (in_array('incorrect_booking_contact_phone_area_code', $keysToFail))
                $data['booking_contact']['phone']['area_code'] = $this->faker->randomNumber(4, true);
            if (in_array('incorrect_type_booking_contact_phone_area_code', $keysToFail))
                $data['booking_contact']['phone']['area_code'] = $this->faker->text(4);
            if (in_array('missed_booking_contact_phone_number', $keysToFail))
                unset($data['booking_contact']['phone']['number']);
            if (in_array('empty_booking_contact_phone_number', $keysToFail))
                $data['booking_contact']['phone']['number'] = '';
            if (in_array('incorrect_booking_contact_phone_number', $keysToFail))
                $data['booking_contact']['phone']['number'] = (string)$this->faker->randomNumber(10, true);
            if (in_array('incorrect_type_booking_contact_phone_number', $keysToFail))
                $data['booking_contact']['phone']['number'] = $this->faker->text(8);
            if (in_array('missed_booking_contact_address', $keysToFail)) unset($data['booking_contact']['address']);
            if (in_array('missed_booking_contact_address_line_1', $keysToFail))
                unset($data['booking_contact']['address']['line_1']);
            if (in_array('incorrect_booking_contact_address_line_1', $keysToFail))
                $data['booking_contact']['address']['line_1'] = $this->faker->text(256);
            if (in_array('missed_booking_contact_address_city', $keysToFail))
                unset($data['booking_contact']['address']['city']);
            if (in_array('incorrect_booking_contact_address_city', $keysToFail))
                $data['booking_contact']['address']['city'] = $this->faker->text(101);
            if (in_array('missed_booking_contact_address_state_province_code', $keysToFail))
                unset($data['booking_contact']['address']['state_province_code']);
            if (in_array('incorrect_booking_contact_address_state_province_code', $keysToFail))
                $data['booking_contact']['address']['state_province_code'] = $this->faker->text(3);
            if (in_array('missed_booking_contact_address_postal_code', $keysToFail))
                unset($data['booking_contact']['address']['postal_code']);
            if (in_array('incorrect_booking_contact_address_postal_code', $keysToFail))
                $data['booking_contact']['address']['postal_code'] = $this->faker->randomNumber(8, true);
            if (in_array('missed_booking_contact_address_country_code', $keysToFail))
                unset($data['booking_contact']['address']['country_code']);
            if (in_array('incorrect_booking_contact_address_country_code', $keysToFail))
                $data['booking_contact']['address']['country_code'] = $this->faker->text(3);
            if (in_array('missed_credit_card_name_card', $keysToFail))
                unset($data['credit_card']['name_card']);
            if (in_array('incorrect_credit_card_name_card', $keysToFail))
                $data['credit_card']['name_card'] = $this->faker->text(1);
            if (in_array('missed_credit_card_number', $keysToFail))
                unset($data['credit_card']['number']);
            if (in_array('incorrect_credit_card_number', $keysToFail))
                $data['credit_card']['number'] = $this->faker->randomNumber(12);
            if (in_array('incorrect_type_credit_card_number', $keysToFail))
                $data['credit_card']['number'] = $this->faker->text(12);
            if (in_array('missed_credit_card_card_type', $keysToFail))
                unset($data['credit_card']['card_type']);
            if (in_array('incorrect_credit_card_card_type', $keysToFail))
                $data['credit_card']['card_type'] = $this->faker->text(5);
            if (in_array('missed_credit_card_expiry_date', $keysToFail))
                unset($data['credit_card']['expiry_date']);
            if (in_array('incorrect_credit_card_expiry_date', $keysToFail))
                $data['credit_card']['expiry_date'] = $this->faker->text(4);
            if (in_array('past_date_credit_card_expiry_date', $keysToFail))
                $data['credit_card']['expiry_date'] = Carbon::now()->subMonths(rand(1, 12))->format('m/Y');
            if (in_array('missed_credit_card_cvv', $keysToFail))
                unset($data['credit_card']['cvv']);
            if (in_array('incorrect_credit_card_cvv', $keysToFail))
                $data['credit_card']['cvv'] = $this->faker->randomNumber(4, true);
            if (in_array('incorrect_type_credit_card_cvv', $keysToFail))
                $data['credit_card']['cvv'] = (string)$this->faker->randomNumber(3, true);
        }

        return $data;
    }

    /**
     * @param bool $withCreditCard
     * @return array
     */
    protected function generateHotelBookData(bool $withCreditCard = true): array
    {
        $data = [
            'amount_pay' => $this->faker->randomElement(['Deposit', 'Full Payment']),
            'booking_contact' => [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->email,
                'phone' => [
                    'country_code' => 1,
                    'area_code' => $this->faker->numberBetween(201, 989),
                    'number' => $this->faker->numerify('########'),
                ],
                'address' => [
                    'line_1' => $this->faker->streetAddress,
                    'city' => $this->faker->city,
                    'state_province_code' => Address::stateAbbr(),
                    'postal_code' => $this->faker->postcode,
                    'country_code' => 'US',
                ],
            ]
        ];

        if ($withCreditCard) {
            $data['credit_card'] = [
                'name_card' => $this->faker->creditCardType,
                'number' => (int)$this->faker->creditCardNumber,
                'card_type' => $this->faker->randomElement(['MSC', 'VISA', 'AMEX', 'DIS']),
                'expiry_date' => $this->faker->creditCardExpirationDate,
                'cvv' => $this->faker->randomNumber(3),
                'billing_address' => $this->faker->streetAddress,
            ];
        }

        return $data;
    }
}
