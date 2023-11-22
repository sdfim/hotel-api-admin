<?php

namespace Feature\API\Booking;

use App\Models\User;
use Feature\API\ApiTestCase;
use Feature\API\Pricing\HotelPricingGeneralMethodsTrait;

class HotelBookingApiTestCase extends ApiTestCase
{
    use HotelPricingGeneralMethodsTrait;

    /**
     * @return void
     */
    protected function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }

    /**
     * @return array
     */
    protected function createHotelBooking(): array
    {
        $pricingSearchRequestResponse = $this->getHotelPricingSearchData();

        $bookingItems = $this->getBookingItems($pricingSearchRequestResponse);

        $bookingAddItemResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-item?booking_item=$bookingItems[0]");

        $bookingId = $bookingAddItemResponse->json('data.booking_id');

        return [
            'booking_id' => $bookingId ?? '',
            'booking_items' => $bookingItems
        ];
    }

    /**
     * @return array
     */
    protected function getHotelPricingSearchData(): array
    {
        $pricingSearchRequestData = $this->generateHotelPricingSearchRequestData();

        return $this->withHeaders($this->headers)
            ->postJson('/api/pricing/search', $pricingSearchRequestData)
            ->json();
    }

    /**
     * @param array $requestResponse
     * @param string $supplier
     * @param int $resultIndex
     * @param int $roomGroupIndex
     * @return array
     */
    protected function getBookingItems(array $requestResponse, string $supplier = 'Expedia', int $resultIndex = 0, int $roomGroupIndex = 0): array
    {
        return array_column($requestResponse['data']['results'][$supplier][$resultIndex]['room_groups'][$roomGroupIndex]['rooms'], 'booking_item') ?? [];
    }
}
