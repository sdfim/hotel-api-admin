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

        // dump($headers);
        // $response->dd();

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

        // dump($headers);
        // $response->dd();

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_failed_type_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithFailedTypeRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        // dump($headers);
        // $response->dd();

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_search_with_failed_destination_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithFailedDestinationRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        // dump($headers);
        // $response->dd();

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
    public function test_hotel_search_with_failed_rating_method_response_400()
    {
        $this->seederSupplier();

        $headers = $this->getHeader();
        $jsonData = $this->hotelSearchWithFailedRatingRequest();
        $response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);

        // dump($headers);
        // $response->dd();

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
    private function hotelSearchWithFailedTypeRequest(): array
    {
        return [
            "type" => "aaaaa",
            "destination" => 1175,
            "rating" => 4,
            "page" => 1,
            "results_per_page" => 250,
        ];
    }

    /**
     * @return array
     */
    private function hotelSearchWithFailedDestinationRequest(): array
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
    private function hotelSearchWithFailedRatingRequest(): array
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
            "latitude" => 40.7480,
            "longitude" => -73.991,
            "radius" => 20,
            "rating" => 1,
            "page" => 1,
            "results_per_page" => 20
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
}
