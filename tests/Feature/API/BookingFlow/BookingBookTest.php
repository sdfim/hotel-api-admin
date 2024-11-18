<?php

namespace Tests\Feature\API\BookingFlow;

use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;

class BookingBookTest extends BaseBookingFlowTest
{
    #[Test]
    public function test_search(): void
    {
        parent::test_search();
    }

    #[Test]
    #[Depends('test_search')]
    public function test_add_booking_item(): void
    {
        parent::test_add_booking_item();
    }

    #[Test]
    #[Depends('test_add_booking_item')]
    public function test_add_passengers(): void
    {
        parent::test_add_passengers();
    }

    #[Test]
    #[Depends('test_add_passengers')]
    public function test_book()
    {
        $response = $this->request()->json('POST', route('book'),
                $this->requestBookData()
            );

        $response->assertStatus(200);
    }

    #[Test]
    #[Depends('test_book')]
    public function test_cancel()
    {
        $response = $this->request()->json('DELETE', route('cancelBooking'),[
            'booking_id' => self::$bookingId
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    #[Depends('test_cancel')]
    public function test_search_again(): void
    {
        $this->test_search();
    }

    #[Test]
    #[Depends('test_search_again')]
    public function test_add_booking_item_again(): void
    {
        $this->test_add_booking_item();
    }

    #[Test]
    #[Depends('test_add_booking_item_again')]
    public function test_add_passengers_again(): void
    {
        $this->test_add_passengers();
    }

    #[Test]
    #[Depends('test_add_passengers_again')]
    public function test_book_again()
    {
        $this->test_book();
    }


    private function requestBookData(): array
    {
        return [
            'booking_id' => self::$bookingId,
            'amount_pay' => 'Deposit',
            'booking_contact' => [
                'first_name' => 'Test',
                'last_name' => 'Test',
                'email' => 'test@gmail.com',
                'phone' => [
                    'country_code' => '1',
                    'area_code' => '487',
                    'number' => '5550077',
                ],
                'address' => [
                    'line_1' => '5047 Kessler Glens', //$faker->streetAddress(),
                    'city' => 'Ortizville', //$faker->city(),
                    'state_province_code' => 'VT', //$faker->stateAbbr(),
                    'postal_code' => 'mt', //$faker->lexify(str_repeat('?', rand(1, 7))), //$faker->postcode(),
                    'country_code' => 'US', //$faker->countryCode(),
                ],
            ],
            'special_requests' => [
                [
                    'booking_item' => self::$bookingItem,
                    'room' => 1,
                    'special_request' => 'UJV Test Booking, please disregard.',
                ],
            ],
            'credit_cards' => [
                [
                    'credit_card' => [
                        'cvv' => '123',
                        'number' => 4001919257537193,
                        'card_type' => 'VISA',
                        'name_card' => 'Visa',
                        'expiry_date' => '09/2026',
                        'billing_address' => null,
                    ],
                    'booking_item' => self::$bookingItem,
                ]
            ]
        ];
    }
}
