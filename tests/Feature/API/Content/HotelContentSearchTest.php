<?php

namespace Tests\Feature\API\Content;

use Feature\API\ApiTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class HotelContentSearchTest extends ApiTestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var array|string[]
     */
    private array $headers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seederSupplier();
        $this->headers = $this->getHeader();
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_method_response_200()
    {
        $jsonData = $this->hotelSearchRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_without_type_method_response_400()
    {
        $jsonData = $this->hotelSearchWithoutTypeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_incorrect_type_method_response_400()
    {
        $jsonData = $this->hotelSearchWithIncorrectTypeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_incorrect_destination_method_response_400()
    {
        $jsonData = $this->hotelSearchWithIncorrectDestinationRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_incorrect_rating_method_response_400()
    {
        $jsonData = $this->hotelSearchWithIncorrectRatingRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'rating' => ['The rating must be a number.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_method_response_200()
    {
        $jsonData = $this->hotelSearchByCoordinatesRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_type_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutTypeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_type_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectTypeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_latitude_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutLatitudeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'latitude' => ['The latitude field is required.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_latitude_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectLatitudeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'latitude' => ['The latitude must be between -90 and 90.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_longitude_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutLongitudeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'longitude' => ['The longitude field is required.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_longitude_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectLongitudeRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'longitude' => ['The longitude must be between -180 and 180.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_radius_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutRadiusRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'radius' => ['The radius field is required.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_radius_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectRadiusRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'radius' => ['The radius must be between 1 and 100.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_rating_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutRatingRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'radius' => ['The radius must be between 1 and 100.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_rating_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectRatingRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'rating' => ['The rating must be between 1 and 5.5.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_page_method_response_200()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutPageRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_page_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectPageRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'page' => ['The page must be between 1 and 1000.']
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_results_per_page_method_response_200()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithoutResultsPerPageRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_results_per_page_method_response_400()
    {
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectResultsPerPageRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'results_per_page' => ['The results per page must be between 1 and 1000.']
                ]
            ]);
    }

    /**
     * @return array
     */
    private function hotelSearchRequestData(): array
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
    private function hotelSearchWithoutTypeRequestData(): array
    {
        return [
            'type' => '',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithIncorrectTypeRequestData(): array
    {
        return [
            'type' => 'wrong_type',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithIncorrectDestinationRequestData(): array
    {
        return [
            'type' => 'hotel',
            'destination' => '',
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithIncorrectRatingRequestData(): array
    {
        return [
            'type' => 'hotel',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => '',
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutTypeRequestData(): array
    {
        return [
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectTypeRequestData(): array
    {
        return [
            'type' => 'wrong_type',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutLatitudeRequestData(): array
    {
        return [
            'type' => 'hotel',
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * Latitude:
     *
     * Valid range: -90° to +90°
     * Northern Hemisphere: 0° to +90°
     * Southern Hemisphere: 0° to -90°
     *
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectLatitudeRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -180, -91),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutLongitudeRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * Longitude:
     *
     * Valid range: -180° to +180°
     * Eastern Hemisphere: 0° to +180°
     * Western Hemisphere: 0° to -180°
     *
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectLongitudeRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -360, -180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutRadiusRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectRadiusRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => -1,
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutRatingRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => -1,
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectRatingRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => -1,
            'page' => 1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutPageRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectPageRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => -1,
            'results_per_page' => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutResultsPerPageRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectResultsPerPageRequestData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => -1
        ];
    }
}
