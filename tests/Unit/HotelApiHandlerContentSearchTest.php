<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;
use Tests\TestCase;

class HotelApiHandlerContentSearchTest extends TestCase
{
    use WithFaker;

    /**
     * @return void
     */
    public function test_hotel_content_search_response_200(): void
    {
        $hotelApiHandler = new HotelApiHandler();

        $request = new Request();
        $request->merge($this->hotelSearchData());

        $response = $hotelApiHandler->search($request);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_content_search_without_type_response_400(): void
    {
        $hotelApiHandler = new HotelApiHandler();

        $request = new Request();
        $request->merge($this->hotelSearchWithoutTypeData());

        $response = $hotelApiHandler->search($request);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @return array
     */
    private function hotelSearchData(): array
    {
        return [
            'type' => 'hotel',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithoutTypeData(): array
    {
        return [
            'type' => '',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }
}
