<?php

namespace Tests\Feature\API\Content;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelContentSearchTest extends TestCase
{
    use RefreshDatabase;

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
    public function test_hotel_search_method_response_true()
    {
        $jsonData = $this->hotelSearchRequest();
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
        $jsonData = $this->hotelSearchWithoutTypeRequest();
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
        $jsonData = $this->hotelSearchWithIncorrectTypeRequest();
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
        $jsonData = $this->hotelSearchWithIncorrectDestinationRequest();
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
        $jsonData = $this->hotelSearchWithIncorrectRatingRequest();
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
    public function test_hotel_search_by_coordinates_method_response_true()
    {
        $jsonData = $this->hotelSearchByCoordinatesRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutTypeRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectTypeRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutLatitudeRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectLatitudeRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutLongitudeRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectLongitudeRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutRadiusRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectRadiusRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutRatingRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectRatingRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutPageRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectPageRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithoutResultsPerPageRequest();
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
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectResultsPerPageRequest();
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
    private function hotelSearchRequest(): array
    {
        return [
            "type" => "hotel",
            "destination" => 1175,
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithoutTypeRequest(): array
    {
        return [
            "type" => "",
            "destination" => 1175,
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithIncorrectTypeRequest(): array
    {
        return [
            "type" => "wrong_type",
            "destination" => 1175,
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithIncorrectDestinationRequest(): array
    {
        return [
            "type" => "hotel",
            "destination" => "",
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithIncorrectRatingRequest(): array
    {
        return [
            "type" => "hotel",
            "destination" => 1175,
            "rating" => "",
            "page" => 1,
            "results_per_page" => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutTypeRequest(): array
    {
        return [
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectTypeRequest(): array
    {
        return [
            "type" => "wrong_type",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutLatitudeRequest(): array
    {
        return [
            "type" => "hotel",
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
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
    private function hotelSearchByCoordinatesWithIncorrectLatitudeRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-180, -91),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutLongitudeRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
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
    private function hotelSearchByCoordinatesWithIncorrectLongitudeRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-360, -180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutRadiusRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectRadiusRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => -1,
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutRatingRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => -1,
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectRatingRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => -1,
            "page" => 1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutPageRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectPageRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => -1,
            "results_per_page" => 20
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithoutResultsPerPageRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchByCoordinatesWithIncorrectResultsPerPageRequest(): array
    {
        return [
            "type" => "hotel",
            "latitude" => $this->randFloat(-90, 90),
            "longitude" => $this->randFloat(-180, 180),
            "radius" => rand(10, 50),
            "rating" => $this->randFloat(1, 5.5),
            "page" => 1,
            "results_per_page" => -1
        ];
    }

    /**
     * @return void
     */
    private function seederSupplier(): void
    {
        $supplier = Supplier::firstOrNew([
            'name' => 'expedia',
            'description' => 'Expedia Description']);
        $supplier->save();
    }

    /**
     * @return string[]
     */
    public function getHeader(): array
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        return [
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * @param float $minValue
     * @param float $maxValue
     * @return float
     */
    public function randFloat(float $minValue, float $maxValue): float
    {
        return round($minValue + mt_rand() / mt_getrandmax() * ($maxValue - $minValue), 2);
    }
}
