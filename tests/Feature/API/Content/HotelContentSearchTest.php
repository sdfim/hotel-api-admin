<?php

namespace Tests\Feature\API\Content;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Tests\TestCase;

class HotelContentSearchTest extends TestCase
{
    use RefreshDatabase;

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

	public function test_hotel_search_without_type_method_response_true()
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

	public function test_hotel_search_with_failed_type_method_response_true()
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

	public function test_hotel_search_with_failed_destination_method_response_true()
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

	public function test_hotel_search_with_failed_rating_method_response_true()
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

	private function seederSupplier() : void
	{
		$supplier = Supplier::firstOrNew([
            'name' => 'expedia',
            'description' => 'Expedia Description']);
        $supplier->save();
	}

    private function hotelSearchRequest() : array
	{
		return [
			"type" => "hotel",
			"destination" => 1175,
			"rating" => 4,
			"page" => 1,
			"results_per_page" => 250,
		];
	}

	private function hotelSearchWithoutTypeRequest() : array
	{
		return [
			"type" => "",
			"destination" => 1175,
			"rating" => 4,
			"page" => 1,
			"results_per_page" => 250,
		];
	}

	private function hotelSearchWithFailedTypeRequest() : array
	{
		return [
			"type" => "aaaaa",
			"destination" => 1175,
			"rating" => 4,
			"page" => 1,
			"results_per_page" => 250,
		];
	}

	private function hotelSearchWithFailedDestinationRequest() : array
	{
		return [
			"type" => "hotel",
			"destination" => "",
			"rating" => 4,
			"page" => 1,
			"results_per_page" => 250,
		];
	}

	private function hotelSearchWithFailedRatingRequest() : array
	{
		return [
			"type" => "hotel",
			"destination" => 1175,
			"rating" => "",
			"page" => 1,
			"results_per_page" => 250,
		];
	}

	public function getHeader() : array
	{
		$user = User::factory()->create();
		$token = $user->createToken('TestToken')->plainTextToken;
		return [
			'Authorization' => 'Bearer ' . $token,
		];
	}
}
