<?php

namespace Tests\Feature\API\Pricing;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HotelPricingSearchTest extends TestCase
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
    public function test_hotel_pricing_search_method_response_200(): void
    {
        $jsonData = $this->hotelSearchRequestData();
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_type_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['type_missed']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

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
    public function test_hotel_pricing_search_with_incorrect_type_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_type']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

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
    public function test_hotel_pricing_search_with_incorrect_currency_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_currency']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "currency" => [
                        "The selected currency is invalid."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_supplier_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_supplier']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "supplier" => [
                        "Incorrect/non-existent supplier"
                    ]
                ]
            ]);
    }


    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_check_in_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_check_in']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "checkin" => [
                        "The checkin must be a date after today."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_check_out_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_check_out']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "checkout" => [
                        "The checkout must be a date after checkin."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_check_in_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['check_in_missed']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "checkin" => [
                        "The checkin field is required."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_check_out_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['check_out_missed']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "checkout" => [
                        "The checkout field is required."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_destination_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_destination']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "destination" => [
                        "The destination must be a non-negative integer."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_destination_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['missed_destination']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "destination" => [
                        "The destination field is required."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_rating_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_rating']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "rating" => [
                        "The rating must be between 1 and 5.5."
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_rating_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['missed_rating']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                "success" => false,
                "error" => [
                    "rating" => [
                        "The rating field is required."
                    ]
                ]
            ]);
    }

    /**
     * @param array $keysToFail
     * @return array
     */
    private function hotelSearchRequestData(array $keysToFail = []): array
    {
        $data = [
            "type" => "hotel",
            "currency" => "EUR",
            "supplier" => "Expedia",
            "hotel_name" => "Sheraton",
            "checkin" => Carbon::now()->addDays(7)->toDateString(),
            "checkout" => Carbon::now()->addDays(7 + rand(2, 5))->toDateString(),
            "destination" => 961,
            "rating" => $this->randFloat(1, 5.5),
            "occupancy" => $this->generateOccupancy()
        ];

        if (count($keysToFail) > 0) {
            if (in_array('incorrect_type', $keysToFail)) $data['type'] = 'wrong_type';
            if (in_array('type_missed', $keysToFail)) unset($data['type']);
            if (in_array('incorrect_currency', $keysToFail)) $data['currency'] = 'Wrong Currency';
            if (in_array('incorrect_supplier', $keysToFail)) $data['supplier'] = 'Wrong Supplier';
            if (in_array('incorrect_check_in', $keysToFail)) $data['checkin'] = Carbon::now()->subDays(5)->toDateString();
            if (in_array('incorrect_check_out', $keysToFail)) $data['checkout'] = Carbon::now()->subDays(2)->toDateString();
            if (in_array('check_in_missed', $keysToFail)) unset($data['checkin']);
            if (in_array('check_out_missed', $keysToFail)) unset($data['checkout']);
            if (in_array('incorrect_destination', $keysToFail)) $data['destination'] = 0;
            if (in_array('missed_destination', $keysToFail)) unset($data['destination']);
            if (in_array('incorrect_rating', $keysToFail)) $data['rating'] = -1;
            if (in_array('missed_rating', $keysToFail)) unset($data['rating']);
            if (in_array('incorrect_occupancy', $keysToFail)) $data['occupancy'] = [[]];
            if (in_array('missed_occupancy', $keysToFail)) unset($data['occupancy']);
            if (in_array('missed_occupancy_adults', $keysToFail)) {
                foreach ($data['occupancy'] as $room) {
                    unset($room['adults']);
                }
            }
            if (in_array('incorrect_occupancy_adults', $keysToFail)) {
                foreach ($data['occupancy'] as $room) {
                    $room['adults'] = 0;
                }
            }
            if (in_array('incorrect_children_count', $keysToFail)) {
                foreach ($data['occupancy'] as $room) {
                    if (isset($room['children'])) $room['children'] = 0;
                }
            }
            if (in_array('missed_children_ages', $keysToFail)) {
                foreach ($data['occupancy'] as $room) {
                    if (isset($room['children']) && isset($room['children_ages'])) unset($room['children_ages']);
                }
            }
            if (in_array('incorrect_children_ages', $keysToFail)) {
                foreach ($data['occupancy'] as $room) {
                    if (isset($room['children']) && isset($room['children_ages'])) $room['children_ages'] = [];
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    private function generateOccupancy(): array
    {
        $roomCount = rand(1, 4);
        $occupancy = [];

        for ($i = 0; $i < $roomCount; $i++) {
            $haveChildren = rand(0, 1);
            $occupancy[$i]['adults'] = rand(1, 3);
            if ($haveChildren) {
                $numberOfChildren = rand(1, 2);
                $occupancy[$i]['children'] = $numberOfChildren;
                for ($c = 0; $c < $numberOfChildren; $c++) {
                    $occupancy[$i]['children_ages'][$c] = rand(1, 12);
                }
            }
        }

        return $occupancy;
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
     * @return void
     */
    private function seederSupplier(): void
    {
        $supplier = Supplier::firstOrNew([
            'name' => 'expedia',
            'description' => 'Expedia Description',
        ]);
        $supplier->save();
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
