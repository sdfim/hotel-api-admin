<?php

namespace Tests\Feature\API\Content;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Tests\TestCase;

class HotelContentDetailTest extends TestCase
{
    use RefreshDatabase;

	public function test_hotel_detail_method_response_true()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest();
		$response_search = $this->withHeaders($headers)->postJson('/api/content/search', $jsonData);
		$hotel_info = $response_search['data']['results'];
		$hotel_info = $hotel_info['Expedia'][0];
		$hotel_id = $hotel_info['giata_hotel_code'];
		
		$response_detail = $this->withHeaders($headers)->get('/api/content/detail?property_id='.$hotel_id.'&type=hotel');
		
		$response_detail
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
    }

	public function test_hotel_detail_false_property_id_method_response_true()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();		
		$response_detail = $this->withHeaders($headers)->get('/api/content/detail?property_id=99999999999999&type=hotel');
		$response_detail
			->assertStatus(400)
			->assertJson([
				'success' => false,
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
