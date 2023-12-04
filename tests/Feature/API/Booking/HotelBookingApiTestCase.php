<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
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
     * @param array $occupancy
     * @return bool
     */
    protected function addPassengersToBookingItem(string $bookingId, string $bookingItem, array $occupancy): bool
    {
        $addPassengersRequestData = $this->generateAddPassengersData($bookingItem, $occupancy);

        $addPassengersRequestResponse = $this->withHeaders($this->headers)
            ->postJson("api/booking/add-passengers?booking_id=$bookingId", $addPassengersRequestData)
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
        $occupancy = $createBooking['hotel_pricing_request_data']['occupancy'];

        $this->addPassengersToBookingItem($bookingId, $bookingItem, $occupancy);

        return $bookingId;
    }

    /**
     * @param string $bookingItem
     * @param array $occupancy
     * @return array[]
     */
    protected function generateAddPassengersData(string $bookingItem, array $occupancy): array
    {
        $genders = ['male', 'female'];

        $addPassengersRequestData = [
            'passengers' => [],
        ];

        foreach ($occupancy as $roomNumber => $room) {
            $numberOfAdultsInRoom = count($room['adults']);
            $numberOfChildrenInRoom = count($room['children_ages']) ?? 0;

            for ($a = 0; $a < $numberOfAdultsInRoom; $a++) {
                $gender = $genders[rand(0, 1)];
                $addPassengersRequestData['passengers'] = [
                    'title' => $gender === 'male' ? 'Mr' : 'Mrs',
                    'given_name' => $this->faker->firstName($gender),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => $this->faker->dateTimeBetween('1980-01-01', '2006-12-31')->format('Y-m-d'),
                    'booking_items' => [
                        [
                            'booking_item' => $bookingItem,
                            'room' => $roomNumber
                        ]
                    ]
                ];
            }

            for ($c = 0; $c < $numberOfChildrenInRoom; $c++) {
                $gender = $genders[rand(0, 1)];
                $addPassengersRequestData[] = [
                    'title' => $gender === 'male' ? 'Mr' : 'Mrs',
                    'given_name' => $this->faker->firstName($gender),
                    'family_name' => $this->faker->lastName(),
                    'date_of_birth' => Carbon::now()->subYears($room['children_ages'][$c])->toDateString(),
                    'booking_items' => [
                        [
                            'booking_item' => $bookingItem,
                            'room' => $roomNumber
                        ]
                    ]
                ];
            }
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
