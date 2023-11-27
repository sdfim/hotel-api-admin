<?php

namespace Tests\Feature\API\Booking;

use Tests\Feature\API\ApiTestCase;
use Tests\Feature\API\Pricing\HotelPricingGeneralMethodsTrait;
use Illuminate\Support\Arr;

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
            'booking_items' => $bookingItems,
            'hotel_pricing_request_data' => $pricingSearchRequestResponse['data']['query']
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
     * @return array
     */
    protected function getBookingItemsFromPricingSearchResult(array $requestResponse, string $supplier = 'Expedia'): array
    {
        $flattenedData = Arr::dot($requestResponse['data']['results'][$supplier]);
        $bookingItems = [];

        foreach ($flattenedData as $key => $value) {
            if (str_contains($key, 'booking_item')) {
                $bookingItems[] = $value;
            }
        }

        return $bookingItems;
    }
}
