<?php

namespace Tests\Feature\API\Booking;

use Tests\Feature\API\ApiTestCase;
use Tests\Feature\API\Pricing\HotelPricingGeneralMethodsTrait;

class HotelBookingApiTestCase extends ApiTestCase
{
    use HotelPricingGeneralMethodsTrait;

    /**
     * @return array
     */
    protected function createHotelBooking(): array
    {
        $pricingSearchRequestResponse = $this->getHotelPricingSearchData();
		
        $bookingItems = $this->getBookingItemsFromPricingSearchResult($pricingSearchRequestResponse);

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
    protected function getBookingItemsFromPricingSearchResult(array $requestResponse, string $supplier = 'Expedia', int $resultIndex = 0, int $roomGroupIndex = 0): array
    {
        return array_column($requestResponse['data']['results'][$supplier][$resultIndex]['room_groups'][$roomGroupIndex]['rooms'], 'booking_item') ?? [];
    }

    /**
     * @param array $requestResponse
     * @param string $supplier
     * @param int $resultIndex
     * @param int $roomGroupIndex
     * @return int
     */
    protected function getNumberOfRoomsFromPricingSearchResult(array $requestResponse, string $supplier = 'Expedia', int $resultIndex = 0, int $roomGroupIndex = 0): int
    {
        return count($requestResponse['data']['results'][$supplier][$resultIndex]['room_groups'][$roomGroupIndex]['rooms']) ?? 0;
    }
}
