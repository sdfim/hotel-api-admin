<?php

namespace Tests\Feature\API\Pricing;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Tests\TestCase;

class HotelPricingSearchTest extends TestCase
{
    use RefreshDatabase;

	public function test_hotel_search_method_response_true()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest();
		$response = $this->withHeaders($headers)->postJson('/api/pricing/search', $jsonData);
		
		// dump($headers);
		// $response->dd();

		$response
			->assertStatus(200)
			->assertJson([
				'success' => true,
			]);
    }

	public function test_chack_fail_checkin()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest('checkin');
		$response = $this->withHeaders($headers)->postJson('/api/pricing/search', $jsonData);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
    }

	public function test_chack_fail_checkout()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest('checkout');
		$response = $this->withHeaders($headers)->postJson('/api/pricing/search', $jsonData);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
    }

	public function test_chack_fail_destination()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest('destination');
		$response = $this->withHeaders($headers)->postJson('/api/pricing/search', $jsonData);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
    }

	public function test_chack_fail_rating()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest('rating');
		$response = $this->withHeaders($headers)->postJson('/api/pricing/search', $jsonData);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
    }

	public function test_chack_fail_occupancy()
    {
		$this->seederSupplier();

		$headers = $this->getHeader();
        $jsonData = $this->hotelSearchRequest('occupancy');
		$response = $this->withHeaders($headers)->postJson('/api/pricing/search', $jsonData);
		
		$response
			->assertStatus(400)
			->assertJson([
				'success' => false,
			]);
    }

    private function hotelSearchRequest(string $fail = '') : array
	{
		$checkin = Carbon::now()->addDays(7)->toDateString();
		$checkout = Carbon::now()->addDays(7 + rand(2,5))->toDateString();

		if ($fail == 'checkin') {
			$checkin = Carbon::now()->subDay()->toDateString();
		}
		if ($fail == 'checkout') {
			$checkout = Carbon::now()->subDay()->toDateString();
		}

		$data =  [
			"type" => "hotel",
			"currency" => "EUR",
			"hotel_name" => "New",
			"checkin" => $checkin,
			"checkout" => $checkout,
			"destination" => 961,
			"rating" => 4,
			"occupancy" => [
				[
					"adults" => 2,
					"children" => 3,
					"children_ages" => [4, 12, 1],
				],
				[
					"adults" => 1,
				],
			],
		];

		if ($fail == 'destination') {
			$data['destination'] = 0;
		}
		if ($fail == 'rating') {
			$data['rating'] = 7;
		}
		if ($fail == 'occupancy') {
			$data['occupancy'] = [
				[
					"adults" => -1,
				],
				[
					"adults" => 1,
				],
			];
		}

		return $data;
	}

	public function getHeader() : array
	{
		$user = User::factory()->create();
		$token = $user->createToken('TestToken')->plainTextToken;
		return [
			'Authorization' => 'Bearer ' . $token,
		];
	}

	private function seederSupplier() : void
	{
		$supplier = Supplier::firstOrNew([
            'name' => 'expedia',
            'description' => 'Expedia Description']);
        $supplier->save();
	}
}
