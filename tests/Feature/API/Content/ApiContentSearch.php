<?php

namespace Tests\Feature\API\Content;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Tests\TestCase;

class ApiContentSearch extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_search_method_response()
    {
		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest();
		$response = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);
		
		//dump($headers);
		//$response->dd();

		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
    }

    private function hotelSearchRequest() : array
	{
		return [
			"type" => "hotel",
			"destination" => 1175,
			"rating" => 2,
			"page" => 1,
			"results_per_page" => 2,
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
