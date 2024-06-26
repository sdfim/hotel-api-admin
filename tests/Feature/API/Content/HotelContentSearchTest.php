<?php

namespace Tests\Feature\API\Content;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\API\ApiTestCase;

class HotelContentSearchTest extends ApiTestCase
{
    use WithFaker;

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_method_response_200(): void
    {
        $hotelSearchData = $this->hotelSearchData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_without_type_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchWithoutTypeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid type',
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_with_incorrect_type_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchWithIncorrectTypeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid type',
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_with_incorrect_destination_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchWithIncorrectDestinationData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_with_incorrect_rating_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchWithIncorrectRatingData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'rating' => ['The rating must be a number.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_method_response_200(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_type_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutTypeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid type',
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_type_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectTypeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid type',
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_latitude_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutLatitudeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'latitude' => ['The latitude field is required when destination is not present.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_latitude_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectLatitudeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'latitude' => ['The latitude must be at least -90.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_longitude_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutLongitudeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'longitude' => ['The longitude field is required when destination is not present.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_longitude_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectLongitudeData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'longitude' => ['The longitude must be at least -180.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_radius_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutRadiusData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'radius' => ['The radius field is required when destination is not present.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_radius_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectRadiusData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'radius' => ['The radius must be between 1 and 100.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_rating_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutRatingData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'radius' => ['The radius must be between 1 and 100.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_rating_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectRatingData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'rating' => ['The rating must be between 1 and 5.5.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_page_method_response_200(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutPageData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_page_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectPageData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'page' => ['The page must be between 1 and 1000.'],
                ],
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_results_per_page_method_response_200(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithoutResultsPerPageData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_results_per_page_method_response_400(): void
    {
        $hotelSearchData = $this->hotelSearchByCoordinatesWithIncorrectResultsPerPageData();

        $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

        $hotelSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'results_per_page' => ['The results per page must be between 1 and 1000.'],
                ],
            ]);
    }

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

    private function hotelSearchWithIncorrectTypeData(): array
    {
        return [
            'type' => 'wrong_type',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    private function hotelSearchWithIncorrectDestinationData(): array
    {
        return [
            'type' => 'hotel',
            'destination' => '',
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    private function hotelSearchWithIncorrectRatingData(): array
    {
        return [
            'type' => 'hotel',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => '',
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    private function hotelSearchByCoordinatesData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutTypeData(): array
    {
        return [
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithIncorrectTypeData(): array
    {
        return [
            'type' => 'wrong_type',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutLatitudeData(): array
    {
        return [
            'type' => 'hotel',
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    /**
     * Latitude:
     *
     * Valid range: -90° to +90°
     * Northern Hemisphere: 0° to +90°
     * Southern Hemisphere: 0° to -90°
     */
    private function hotelSearchByCoordinatesWithIncorrectLatitudeData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -180, -91),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutLongitudeData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    /**
     * Longitude:
     *
     * Valid range: -180° to +180°
     * Eastern Hemisphere: 0° to +180°
     * Western Hemisphere: 0° to -180°
     */
    private function hotelSearchByCoordinatesWithIncorrectLongitudeData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -360, -180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutRadiusData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithIncorrectRadiusData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => -1,
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutRatingData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => -1,
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithIncorrectRatingData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => -1,
            'page' => 1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutPageData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithIncorrectPageData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => -1,
            'results_per_page' => 20,
        ];
    }

    private function hotelSearchByCoordinatesWithoutResultsPerPageData(): array
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

    private function hotelSearchByCoordinatesWithIncorrectResultsPerPageData(): array
    {
        return [
            'type' => 'hotel',
            'latitude' => $this->faker->randomFloat(2, -90, 90),
            'longitude' => $this->faker->randomFloat(2, -180, 180),
            'radius' => rand(10, 50),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => -1,
        ];
    }
}
