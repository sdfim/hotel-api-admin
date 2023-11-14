<?php

namespace Tests\Feature\API\Content;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Tests\TestCase;

class HotelContentDestinationTest extends TestCase
{
    use RefreshDatabase;

	public function test_hotel_destination_method_response_true()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
		
		$response_detail = $this->withHeaders($headers)->get('/api/content/destinations?city=London');
		
		$response_detail
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
    }

	public function test_hotel_destination_with_empty_parameter_method_response_true()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
		
		$response_detail = $this->withHeaders($headers)->get('/api/content/destinations?city=');
		
		$response_detail
			->assertStatus(400)
			->assertJson([
				'error' => "Invalid city",
			]);
    }

	public function test_hotel_destination_without_parameter_method_response_true()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
		
		$response_detail = $this->withHeaders($headers)->get('/api/content/destinations');
		
		$response_detail
			->assertStatus(400)
			->assertJson([
				'error' => "Invalid city",
			]);
    }
	
	private function seederSupplier() : void
	{
		$supplier = Supplier::firstOrNew([
            'name' => 'Expedia',
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
