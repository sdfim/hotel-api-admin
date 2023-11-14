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
     * @test
     * @return void
     */
    public function test_hotel_search_method_response_true()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

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
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithoutTypeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_incorrect_type_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithIncorrectTypeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_incorrect_destination_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithIncorrectDestinationRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect destination'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_incorrect_rating_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithIncorrectRatingRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect rating'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_method_response_true()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

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
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutTypeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Type isn\'t provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_type_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectTypeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect type'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_latitude_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutLatitudeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Latitude is not provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_latitude_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectLatitudeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect latitude'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_longitude_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutLongitudeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Longitude is not provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_longitude_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectLongitudeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect longitude'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_radius_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutRadiusRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Radius is not provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_radius_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectRadiusRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect radius'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_rating_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutRatingRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Rating is not provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_rating_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectRatingRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect rating'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_page_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutPageRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Page is not provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_page_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectPageRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect page'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_without_results_per_page_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithoutResultsPerPageRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'results_per_page is not provided'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_by_coordinates_with_incorrect_results_per_page_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchByCoordinatesWithIncorrectResultsPerPageRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Incorrect results_per_page'
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
            "rating" => 4,
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
            "rating" => 4,
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
            "rating" => 4,
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
            "rating" => 4,
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
            "radius" => 20,
            "rating" => 1,
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
            "radius" => 20,
            "rating" => 1,
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
            "radius" => 20,
            "rating" => 1,
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
            "radius" => 20,
            "rating" => 1,
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
            "radius" => 20,
            "rating" => 1,
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
            "radius" => 20,
            "rating" => 1,
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
            "radius" => 20,
            "rating" => 1,
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
            "rating" => 1,
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
            "rating" => 1,
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
            "radius" => -1,
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
            "radius" => 20,
            "rating" => 2,
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
            "radius" => 20,
            "rating" => 2,
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
            "radius" => 20,
            "rating" => 2,
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
            "radius" => 20,
            "rating" => 2,
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
