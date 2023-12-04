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
        $addPassengersRequestData = $this->generateAddPassengersData($roomsCount);

        $addPassengersRequestResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_item=$bookingItem&booking_id=$bookingId", $addPassengersRequestData)
            ->json();

        return $addPassengersRequestResponse['success'];
    }

    /**
     * @return string
     */
    protected function createHotelBookingAndAddPassengersToBookingItem(): string
    {
        $createBooking = $this->createHotelBooking();

        $bookingId = $createBooking['booking_id'];
        $bookingItem = $createBooking['booking_items'][0];

        $roomsCount = count($createBooking['hotel_pricing_request_data']['occupancy']);

        $this->addPassengersToBookingItem($bookingId, $bookingItem, $roomsCount);

        return $bookingId;
    }

    /**
     * @param int $roomsCount
     * @return array[]
     */
    protected function generateAddPassengersData(int $roomsCount): array
    {
        $genders = ['male', 'female'];

        $addPassengersRequestData = [
            'rooms' => [],
        ];

        for ($i = 0; $i < $roomsCount; $i++) {
            $gender = $genders[rand(0, 1)];
            $addPassengersRequestData['rooms'][$i] = [
                'title' => $gender === 'male' ? 'Mr' : 'Mrs',
                'given_name' => $this->faker->firstName($gender),
                'family_name' => $this->faker->lastName(),
                'date_of_birth' => $this->faker->dateTimeBetween('1980-01-01', '2006-12-31')
                    ->format('Y-m-d'),
            ];
        }

        return $addPassengersRequestData;
    }

    /**
     * @return array
     */
    protected function getHotelPricingSearchData(): array
    {
        $pricingSearchRequestData = $this->generateHotelPricingSearchData();

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
