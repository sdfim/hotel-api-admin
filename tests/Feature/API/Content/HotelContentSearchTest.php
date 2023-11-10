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

	public function getHeader() : array
	{
		$user = User::factory()->create();
		$token = $user->createToken('TestToken')->plainTextToken;
		return [
			'Authorization' => 'Bearer ' . $token,
		];
	}
}
