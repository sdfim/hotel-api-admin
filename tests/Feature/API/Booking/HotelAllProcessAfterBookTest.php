<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Support\Carbon;

class HotelAllProcessAfterBookTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @coversNothing
     * @return void
     */
    public function test_book_method_response()
    {
        ## SEARCH 1

        # step 1 Search endpoint api/pricing/search
        $jsonData = $this->searchRequest();
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);
        $responseArr = $response->json();

        $search_id = $responseArr['data']['search_id'];
        $booking_item = $responseArr['data']['results']['Expedia'][0]['room_groups'][0]['rooms'][0]['booking_item'];
        dump($booking_item, $search_id);

        # step 2 add to cart api/pricing/add-item
        $response = $this->withHeaders($this->headers)->postJson('/api/booking/add-item', ['booking_item' => $booking_item]);
        $responseArr = $response->json();
        $booking_id = $responseArr['data']['booking_id'];
        dump($booking_id);

        # step 3 add passenger api/booking/add-passengers
        $jsonData = $this->addPassengersRequest();
        $jsonData = array_merge($jsonData, ['booking_id' => $booking_id, 'booking_item' => $booking_item]);
        $response = $this->withHeaders($this->headers)->postJson('/api/booking/add-passengers', $jsonData);
        $responseArr = $response->json();
        dump($responseArr);

        # step 4 book api/booking/book
        $jsonData = $this->addBookRequest();
        $jsonData = array_merge($jsonData, ['booking_id' => $booking_id]);
        $response = $this->withHeaders($this->headers)->postJson('/api/booking/book', $jsonData);
        $responseArr = $response->json();
        dump($responseArr);

        # step 5 book api/booking/change-booking
        $jsonData = $this->changeBookingRequest();
        $jsonData = array_merge($jsonData, ['booking_id' => $booking_id, 'booking_item' => $booking_item]);
        $response = $this->withHeaders($this->headers)->putJson('/api/booking/change-booking', $jsonData);
        $responseArr = $response->json();
        dump($responseArr);

        # step 6 book api/booking/list-booking
        $response = $this->withHeaders($this->headers)->get('/api/booking/list-bookings?type=hotel&supplier=Expedia');
        $responseArr = $response->json();
        dump($responseArr);

        # step 7 book api/booking/retrieve-booking
        $response = $this->withHeaders($this->headers)->get('/api/booking/retrieve-booking?booking_id=' . $booking_id);
        $responseArr = $response->json();
        dump($responseArr);

        # step 8 book api/booking/cancel-booking
        $response = $this->withHeaders($this->headers)->deleteJson('/api/booking/cancel-booking', ['booking_id' => $booking_id]);
        $responseArr = $response->json();
        dump($responseArr);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'success',
            ]);
    }

    /**
     * @return array[]
     */
    private function changeBookingRequest(): array
    {
        return [
            'query' =>
                [
                    'given_name' => 'John',
                    'family_name' => 'Smith',
                    'smoking' => false,
                    'special_request' => 'Top floor or away fro-street please',
                    'loyalty_id' => 'ABC123'
                ]
        ];
    }

    /**
     * @return array
     */
    private function searchRequest(): array
    {
        $checkin = Carbon::now()->addDays(7)->toDateString();
        $checkout = Carbon::now()->addDays(7 + rand(2, 5))->toDateString();

        return [
            'type' => 'hotel',
            'currency' => 'EUR',
            'hotel_name' => 'New',
            'checkin' => $checkin,
            'checkout' => $checkout,
            'destination' => 961,
            'rating' => 4,
            'occupancy' => [
                [
                    'adults' => 2,
                    'children_ages' => [4, 12, 1],
                ],
                [
                    'adults' => 1,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function searchRequestStep2(): array
    {
        $checkin = Carbon::now()->addDays(7)->toDateString();
        $checkout = Carbon::now()->addDays(7 + rand(2, 5))->toDateString();
        return [
            'type' => 'hotel',
            'checkin' => $checkin,
            'checkout' => $checkout,
            'destination' => 961,
            'rating' => 4.5,
            'occupancy' => [
                [
                    'adults' => 2,
                    'children' => 1
                ],
                [
                    'adults' => 3
                ],
                [
                    'adults' => 1
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function addPassengersRequest(): array
    {
        return [
            'rooms' => [
                [
					'title' => 'mr',
                    'given_name' => 'John',
                    'family_name' => 'Portman',
					'date_of_birth' => '1988-12-14'
				],
                [
					'title' => 'mr',
                    'given_name' => 'John',
                    'family_name' => 'Portman',
					'date_of_birth' => '1988-12-14'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function addPassengersRequestStep2(): array
    {
        return [
            'rooms' => [
                [
					'title' => 'mr',
                    'given_name' => 'John',
                    'family_name' => 'Portman',
					'date_of_birth' => '1988-12-14'
                ],
                [
					'title' => 'ms',
                    'given_name' => 'Dana',
                    'family_name' => 'Portman',
					'date_of_birth' => '1988-12-14'
                ],
                [
					'title' => 'mr',
                    'given_name' => 'Mikle',
                    'family_name' => 'Portman',
					'date_of_birth' => '1988-12-14'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function addBookRequest(): array
    {
        return [
            'amount_pay' => 'Deposit',
            'booking_contact' => [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john@example.com',
                'phone' => [
                    'country_code' => '1',
                    'area_code' => '487',
                    'number' => '5550077'
                ],
                'address' => [
                    'line_1' => '555 1st St',
                    'city' => 'Seattle',
                    'state_province_code' => 'WA',
                    'postal_code' => '98121',
                    'country_code' => 'US'
                ]
            ]
        ];
    }
}
