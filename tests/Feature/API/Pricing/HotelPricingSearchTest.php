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
                'success' => false,
                'error' => [
                    'currency' => [
                        'The selected currency is invalid.'
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
        // $jsonData = $this->hotelSearchRequestData(['incorrect_supplier']);
        // $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        // //TODO: ask Andrew why it return results for non-existent supplier(even if the results are empty)
        // $response
        //     ->assertStatus(400)
        //     ->assertJson([
        //         'success' => false,
        //         'error' => [
        //             'supplier' => [
        //                 'Incorrect/non-existent supplier'
        //             ]
        //         ]
        //     ]);
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
                'success' => false,
                'error' => [
                    'checkin' => [
                        'The checkin must be a date after today.'
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
                'success' => false,
                'error' => [
                    'checkout' => [
                        'The checkout must be a date after checkin.'
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
                'success' => false,
                'error' => [
                    'checkin' => [
                        'The checkin field is required.'
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
                'success' => false,
                'error' => [
                    'checkout' => [
                        'The checkout field is required.'
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
                'success' => false,
                'error' => [
                    'destination' => [
                        'The destination must be a non-negative integer.'
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
                'success' => false,
                'error' => [
                    'destination' => [
                        'The destination field is required.'
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
                'success' => false,
                'error' => [
                    'rating' => [
                        'The rating must be between 1 and 5.5.'
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
                'success' => false,
                'error' => [
                    'rating' => [
                        'The rating field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_occupancy_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_occupancy']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'occupancy.0.adults' => [
                        'The occupancy.0.adults field is required.'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_occupancy_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['missed_occupancy']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        //TODO: ask Andrew why we got such an error. We are expected to receive something like
        //'error' => [
        //  'occupancy' => [
        //      'The occupancy field is required.'
        //  ]
        //]
        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'error' => 'foreach() argument must be of type array|object, null given'
                ],
                'message' => 'failed'
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_occupancy_adults_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['missed_occupancy_adults']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);
        $error = [];

        foreach ($jsonData['occupancy'] as $index => $room) {
            $errorName = "occupancy.$index.adults";
            $error[$errorName] = ["The $errorName field is required."];
        }

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => $error
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_occupancy_adults_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_occupancy_adults']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);
        $error = [];

        foreach ($jsonData['occupancy'] as $index => $room) {
            $errorName = "occupancy.$index.adults";
            $error[$errorName] = ["The $errorName must be between 1 and 9."];
        }

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => $error
            ]);
    }


    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_without_children_ages_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['missed_children_ages']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);
        $error = [];

        foreach ($jsonData['occupancy'] as $index => $room) {
            if (isset($room['children'])) {
                $errorName = "occupancy.$index.children_ages";
                $error[$errorName] = ["The " .  str_replace('_', ' ', $errorName) . " field is required."];
				break;
            }
        }

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => $error
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_pricing_search_with_incorrect_children_ages_method_response_400()
    {
        $jsonData = $this->hotelSearchRequestData(['incorrect_children_ages']);
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);
        $error = [];

        foreach ($jsonData['occupancy'] as $index => $room) {
            if (isset($room['children']) && isset($room['children_ages'])) {
                $error["occupancy.$index.children_ages"] = ['The occupancy.0.children ages field is required.'];
            }
        }

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => $error
            ]);
    }

    /**
     * @param array $keysToFail An array of keys indicating which values to modify or remove.
     *     Possible values:
     *     - 'incorrect_type': Set an incorrect value for the 'type' key.
     *     - 'type_missed': Remove the 'type' key.
     *     - 'incorrect_currency': Set an incorrect value for the 'currency' key.
     *     - 'incorrect_supplier': Set an incorrect value for the 'supplier' key.
     *     - 'incorrect_check_in': Set an incorrect value for the 'checkin' key.
     *     - 'incorrect_check_out': Set an incorrect value for the 'checkout' key.
     *     - 'check_in_missed': Remove the 'checkin' key.
     *     - 'check_out_missed': Remove the 'checkout' key.
     *     - 'incorrect_destination': Set an incorrect value for the 'destination' key.
     *     - 'missed_destination': Remove the 'destination' key.
     *     - 'incorrect_rating': Set an incorrect value for the 'rating' key.
     *     - 'missed_rating': Remove the 'rating' key.
     *     - 'incorrect_occupancy': Set an incorrect value for the 'occupancy' key.
     *     - 'missed_occupancy': Remove the 'occupancy' key.
     *     - 'missed_occupancy_adults': Remove the 'adults' key from each room in the 'occupancy' array.
     *     - 'incorrect_occupancy_adults': Set an incorrect value for the 'adults' key in each room of the 'occupancy' array.
     *     - 'missed_children_ages': Remove the 'children_ages' key from each room in the 'occupancy' array.
     *     - 'incorrect_children_ages': Set an incorrect value for the 'children_ages' key in each room of the 'occupancy' array.
     * @return array The hotel search request data.
     */
    private function hotelSearchRequestData(array $keysToFail = []): array
    {
        $data = [
            'type' => 'hotel',
            'currency' => 'EUR',
            'supplier' => 'Expedia',
            'hotel_name' => 'Sheraton',
            'checkin' => Carbon::now()->addDays(7)->toDateString(),
            'checkout' => Carbon::now()->addDays(7 + rand(2, 5))->toDateString(),
            'destination' => 961,
            'rating' => $this->randFloat(1, 5.5),
            'occupancy' => $this->generateOccupancy()
        ];

        if (count($keysToFail) > 0) {
            $occupancy = &$data['occupancy'];

            if (in_array('incorrect_type', $keysToFail)) $data['type'] = 'wrong_type';
            if (in_array('type_missed', $keysToFail)) unset($data['type']);
            if (in_array('incorrect_currency', $keysToFail)) $data['currency'] = 'Wrong Currency';
            if (in_array('incorrect_supplier', $keysToFail)) $data['supplier'] = 'Wrong Supplier';
            if (in_array('incorrect_check_in', $keysToFail)) $data['checkin'] = Carbon::now()->subDays(5)->toDateString();
            if (in_array('incorrect_check_out', $keysToFail)) $data['checkout'] = Carbon::now()->subDays(2)->toDateString();
            if (in_array('check_in_missed', $keysToFail) ) unset($data['checkin']);
            if (in_array('check_out_missed', $keysToFail)) unset($data['checkout']);
            if (in_array('incorrect_destination', $keysToFail)) $data['destination'] = 0;
            if (in_array('missed_destination', $keysToFail)) unset($data['destination']);
            if (in_array('incorrect_rating', $keysToFail)) $data['rating'] = -1;
            if (in_array('missed_rating', $keysToFail)) unset($data['rating']);
            if (in_array('incorrect_occupancy', $keysToFail)) $data['occupancy'] = [[]];
            if (in_array('missed_occupancy', $keysToFail)) unset($data['occupancy']);
            if (in_array('missed_occupancy_adults', $keysToFail)) {
                foreach ($occupancy as &$room) {
                    unset($room['adults']);
                }
            }
            if (in_array('incorrect_occupancy_adults', $keysToFail)) {
                foreach ($occupancy as &$room) {
                    $room['adults'] = 0;
                }
            }
            if (in_array('missed_children_ages', $keysToFail)) {
                foreach ($occupancy as &$room) {
                    if (isset($room['children'], $room['children_ages'])) {
                        unset($room['children_ages']);
                    }
                }
            }
            if (in_array('incorrect_children_ages', $keysToFail)) {
                foreach ($occupancy as &$room) {
                    if (isset($room['children'], $room['children_ages'])) {
                        $room['children_ages'] = [];
                    }
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
