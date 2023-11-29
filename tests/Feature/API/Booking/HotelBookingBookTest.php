<?php

namespace Feature\API\Booking;

use Carbon\Carbon;
use Illuminate\Support\Str;
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
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $bookingItem = $createBooking['booking_items'][0];

        $roomsCount = count($createBooking['hotel_pricing_request_data']['occupancy']);

        $this->addPassengersToBookingItem($bookingId, $bookingItem, $roomsCount);

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
     * @param array $keysToFail An array of keys indicating which values to modify or remove.
     * Possible values:
     *   - 'incorrect_amount_pay': Set an incorrect value for 'amount_pay'.
     *   - 'type_amount_pay': Unset 'amount_pay'.
     *   - 'missed_booking_contact': Unset 'booking_contact'.
     *   - 'missed_booking_contact_first_name': Unset 'booking_contact'['first_name'].
     *   - 'incorrect_booking_contact_first_name': Set an incorrect value for 'booking_contact'['first_name'].
     *   - 'missed_booking_contact_last_name': Unset 'booking_contact'['last_name'].
     *   - 'incorrect_booking_contact_last_name': Set an incorrect value for 'booking_contact'['last_name'].
     *   - 'missed_booking_contact_email': Unset 'booking_contact'['email'].
     *   - 'incorrect_booking_contact_email': Set an incorrect value for 'booking_contact'['email'].
     *   - 'missed_booking_contact_phone': Unset 'booking_contact'['phone'].
     *   - 'missed_booking_contact_phone_country_code': Unset 'booking_contact'['phone']['country_code'].
     *   - 'incorrect_booking_contact_phone_country_code': Set an incorrect value for 'booking_contact'['phone']['country_code'].
     *   - 'incorrect_type_booking_contact_phone_country_code': Set an incorrect type for 'booking_contact'['phone']['country_code'].
     *   - 'missed_booking_contact_phone_area_code': Unset 'booking_contact'['phone']['area_code'].
     *   - 'incorrect_booking_contact_phone_area_code': Set an incorrect value for 'booking_contact'['phone']['area_code'].
     *   - 'incorrect_type_booking_contact_phone_area_code': Set an incorrect type for 'booking_contact'['phone']['area_code'].
     *   - 'missed_booking_contact_phone_number': Unset 'booking_contact'['phone']['number'].
     *   - 'incorrect_booking_contact_phone_number': Set an incorrect value for 'booking_contact'['phone']['number'].
     *   - 'incorrect_type_booking_contact_phone_number': Set an incorrect type for 'booking_contact'['phone']['number'].
     *   - 'missed_booking_contact_address': Unset 'booking_contact'['address'].
     *   - 'missed_booking_contact_address_line_1': Unset 'booking_contact'['address']['line_1'].
     *   - 'incorrect_booking_contact_address_line_1': Set an incorrect value for 'booking_contact'['address']['line_1'].
     *   - 'missed_booking_contact_address_city': Unset 'booking_contact'['address']['city'].
     *   - 'incorrect_booking_contact_address_city': Set an incorrect value for 'booking_contact'['address']['city'].
     *   - 'missed_booking_contact_address_state_province_code': Unset 'booking_contact'['address']['state_province_code'].
     *   - 'incorrect_booking_contact_address_state_province_code': Set an incorrect value for 'booking_contact'['address']['state_province_code'].
     *   - 'missed_booking_contact_address_postal_code': Unset 'booking_contact'['address']['postal_code'].
     *   - 'incorrect_booking_contact_address_postal_code': Set an incorrect value for 'booking_contact'['address']['postal_code'].
     *   - 'incorrect_type_booking_contact_address_postal_code': Set an incorrect type for 'booking_contact'['address']['postal_code'].
     *   - 'missed_booking_contact_address_country_code': Unset 'booking_contact'['address']['country_code'].
     *   - 'incorrect_booking_contact_address_country_code': Set an incorrect value for 'booking_contact'['address']['country_code'].
     *   - 'missed_credit_card_name_card': Unset 'credit_card'['name_card'].
     *   - 'incorrect_credit_card_name_card': Set an incorrect value for 'credit_card'['name_card'].
     *   - 'missed_credit_card_number': Unset 'credit_card'['number'].
     *   - 'incorrect_credit_card_number': Set an incorrect value for 'credit_card'['number'].
     *   - 'incorrect_type_credit_card_number': Set an incorrect type for 'credit_card'['number'].
     *   - 'missed_credit_card_card_type': Unset 'credit_card'['card_type'].
     *   - 'incorrect_credit_card_card_type': Set an incorrect value for 'credit_card'['card_type'].
     *   - 'missed_credit_card_expiry_date': Unset 'credit_card'['expiry_date'].
     *   - 'incorrect_credit_card_expiry_date': Set an incorrect value for 'credit_card'['expiry_date'].
     *   - 'past_date_credit_card_expiry_date': Set a past date for 'credit_card'['expiry_date'].
     *   - 'missed_credit_card_cvv': Unset 'credit_card'['cvv'].
     *   - 'incorrect_credit_card_cvv': Set an incorrect value for 'credit_card'['cvv'].
     *   - 'incorrect_type_credit_card_cvv': Set an incorrect type for 'credit_card'['cvv'].
     *   - 'missed_credit_card_billing_address': Unset 'credit_card'['billing_address'].
     *   - 'incorrect_credit_card_billing_address': Set an incorrect value for 'credit_card'['billing_address'].
     * @return array The hotel search request data.
     */
    private function hotelBookData(array $keysToFail = []): array
    {
        $data = $this->generateHotelBookData();

        if (count($keysToFail) > 0) {
            if (in_array('incorrect_amount_pay', $keysToFail)) $data['amount_pay'] = $this->faker->text(10);
            if (in_array('type_amount_pay', $keysToFail)) unset($data['amount_pay']);
            if (in_array('missed_booking_contact', $keysToFail)) unset($data['booking_contact']);
            if (in_array('missed_booking_contact_first_name', $keysToFail)) unset($data['booking_contact']['first_name']);
            if (in_array('incorrect_booking_contact_first_name', $keysToFail))
                $data['booking_contact']['first_name'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_last_name', $keysToFail)) unset($data['booking_contact']['last_name']);
            if (in_array('incorrect_booking_contact_last_name', $keysToFail))
                $data['booking_contact']['last_name'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_email', $keysToFail)) unset($data['booking_contact']['email']);
            if (in_array('incorrect_booking_contact_email', $keysToFail))
                $data['booking_contact']['email'] = $this->faker->text(10);
            if (in_array('missed_booking_contact_phone', $keysToFail)) unset($data['booking_contact']['phone']);
            if (in_array('missed_booking_contact_phone_country_code', $keysToFail))
                unset($data['booking_contact']['phone']['country_code']);
            if (in_array('incorrect_booking_contact_phone_country_code', $keysToFail))
                $data['booking_contact']['phone']['country_code'] = '';
            if (in_array('incorrect_type_booking_contact_phone_country_code', $keysToFail))
                $data['booking_contact']['phone']['country_code'] = $this->faker->randomNumber(1);
            if (in_array('missed_booking_contact_phone_area_code', $keysToFail))
                unset($data['booking_contact']['phone']['area_code']);
            if (in_array('incorrect_booking_contact_phone_area_code', $keysToFail))
                $data['booking_contact']['phone']['area_code'] = '';
            if (in_array('incorrect_type_booking_contact_phone_area_code', $keysToFail))
                $data['booking_contact']['phone']['area_code'] = $this->faker->randomNumber(3);
            if (in_array('missed_booking_contact_phone_number', $keysToFail))
                unset($data['booking_contact']['phone']['number']);
            if (in_array('incorrect_booking_contact_phone_number', $keysToFail))
                $data['booking_contact']['phone']['number'] = (string)$this->faker->randomNumber(4);
            if (in_array('incorrect_type_booking_contact_phone_number', $keysToFail))
                $data['booking_contact']['phone']['number'] = $this->faker->randomNumber(4);
            if (in_array('missed_booking_contact_address', $keysToFail)) unset($data['booking_contact']['address']);
            if (in_array('missed_booking_contact_address_line_1', $keysToFail))
                unset($data['booking_contact']['address']['line_1']);
            if (in_array('incorrect_booking_contact_address_line_1', $keysToFail))
                $data['booking_contact']['address']['line_1'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_address_city', $keysToFail))
                unset($data['booking_contact']['address']['city']);
            if (in_array('incorrect_booking_contact_address_city', $keysToFail))
                $data['booking_contact']['address']['city'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_address_state_province_code', $keysToFail))
                unset($data['booking_contact']['address']['state_province_code']);
            if (in_array('incorrect_booking_contact_address_state_province_code', $keysToFail))
                $data['booking_contact']['address']['state_province_code'] = $this->faker->text(2);
            if (in_array('missed_booking_contact_address_postal_code', $keysToFail))
                unset($data['booking_contact']['address']['postal_code']);
            if (in_array('incorrect_booking_contact_address_postal_code', $keysToFail))
                $data['booking_contact']['address']['postal_code'] = $this->faker->text(2);
            if (in_array('incorrect_type_booking_contact_address_postal_code', $keysToFail))
                $data['booking_contact']['address']['postal_code'] = $this->faker->randomNumber(5);
            if (in_array('missed_booking_contact_address_country_code', $keysToFail))
                unset($data['booking_contact']['address']['country_code']);
            if (in_array('incorrect_booking_contact_address_country_code', $keysToFail))
                $data['booking_contact']['address']['country_code'] = $this->faker->text(2);
            if (in_array('missed_credit_card_name_card', $keysToFail))
                unset($data['credit_card']['name_card']);
            if (in_array('incorrect_credit_card_name_card', $keysToFail))
                $data['credit_card']['name_card'] = $this->faker->text(2);
            if (in_array('missed_credit_card_number', $keysToFail))
                unset($data['credit_card']['number']);
            if (in_array('incorrect_credit_card_number', $keysToFail))
                $data['credit_card']['number'] = $this->faker->randomNumber(12);
            if (in_array('incorrect_type_credit_card_number', $keysToFail))
                $data['credit_card']['number'] = (string)$this->faker->randomNumber(14);
            if (in_array('missed_credit_card_card_type', $keysToFail))
                unset($data['credit_card']['card_type']);
            if (in_array('incorrect_credit_card_card_type', $keysToFail))
                $data['credit_card']['card_type'] = $this->faker->text(2);
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
            if (in_array('missed_credit_card_billing_address', $keysToFail))
                unset($data['credit_card']['billing_address']);
            if (in_array('incorrect_credit_card_billing_address', $keysToFail))
                $data['credit_card']['billing_address'] = $this->faker->text(2);
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
                    'country_code' => '1',
                    'area_code' => (string)$this->faker->numberBetween(201, 989),
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
            $cardType = $this->faker->creditCardType;
            $data['credit_card'] = [
                'name_card' => $cardType,
                'number' => (int)$this->faker->creditCardNumber,
                'card_type' => strtoupper($cardType),
                'expiry_date' => $this->faker->creditCardExpirationDate,
                'cvv' => $this->faker->randomNumber(3),
                'billing_address' => $this->faker->streetAddress,
            ];
        }

        return $data;
    }
}
