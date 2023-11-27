<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\API\ApiTestCase;
use Tests\Feature\API\Pricing\HotelPricingGeneralMethodsTrait;
use Illuminate\Support\Arr;

class HotelBookingApiTestCase extends ApiTestCase
{
    use HotelPricingGeneralMethodsTrait;
    use WithFaker;

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
     * @param string $bookingId
     * @param string $bookingItem
     * @param int $roomsCount
     * @return bool
     */
    protected function addPassengersToBookingItem(string $bookingId, string $bookingItem, int $roomsCount): bool
    {
        $addPassengersRequestData = [
            'title' => "Add passengers {$this->faker->date}",
            'first_name' => $this->faker->firstName('male'),
            'last_name' => $this->faker->lastName(),
            'rooms' => [],
        ];

        for ($i = 0; $i < $roomsCount; $i++) {
            $addPassengersRequestData['rooms'][$i] = [
                'given_name' => $this->faker->firstName('male'),
                'family_name' => $this->faker->lastName()
            ];
        }

        $addPassengersRequestResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$bookingItem&booking_id=$bookingId", $addPassengersRequestData)
            ->json();

        return $addPassengersRequestResponse['success'];
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
