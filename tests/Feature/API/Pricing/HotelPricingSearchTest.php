<?php

namespace Tests\Feature\API\Pricing;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
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
    public function test_hotel_search_method_response_true(): void
    {
        $jsonData = $this->hotelSearchRequest();
        $response = $this->makeApiRequest('/api/pricing/search', $jsonData);
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @param $jsonData
     * @return void
     */
    public function test_invalid_hotel_search($jsonData): void
    {
        $response = $this->makeApiRequest('/api/pricing/search', $jsonData);
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @return array[]
     */
    public function provideInvalidHotelSearchData(): array
    {
        return [
            [$this->hotelSearchRequest('checkin')],
            [$this->hotelSearchRequest('checkout')],
            [$this->hotelSearchRequest('destination')],
            [$this->hotelSearchRequest('rating')],
            [$this->hotelSearchRequest('occupancy')],
        ];
    }

    /**
     * @test
     * @return void
     */
    public function test_child_ages_count_matches_children_count()
    {
        $jsonData = $this->hotelSearchRequest('child_ages_count_mismatch');
        $response = $this->makeApiRequest('/api/pricing/search', $jsonData);
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @param string $url
     * @param array $jsonData
     * @return TestResponse
     */
    private function makeApiRequest(string $url, array $jsonData): TestResponse
    {
        return $this->withHeaders($this->headers)->postJson($url, $jsonData);
    }

    /**
     * @param string $fail
     * @return array
     */
    private function hotelSearchRequest(string $fail = ''): array
    {
        $checkin = Carbon::now()->addDays(7)->toDateString();
        $checkout = Carbon::now()->addDays(7 + rand(2, 5))->toDateString();

        if ($fail == 'checkin') {
            $checkin = Carbon::now()->subDay()->toDateString();
        }
        if ($fail == 'checkout') {
            $checkout = Carbon::now()->subDay()->toDateString();
        }

        $data = [
            'type' => 'hotel',
            'currency' => 'EUR',
            'hotel_name' => 'New',
            'checkin' => $checkin,
            'checkout' => $checkout,
            'destination' => 961,
            'rating' => 4,
            'occupancy' => [
                [
                    'adults' => 2,
                    'children' => 3,
                    'children_ages' => [4, 12, 1],
                ],
                [
                    'adults' => 1,
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
                    'adults' => -1,
                ],
                [
                    'adults' => 1,
                ],
            ];
        }
        if ($fail == 'child_ages_count_mismatch') {
            $data['occupancy'] = [
                [
                    'adults' => 2,
                    'children' => 3,
                    'children_ages' => [4, 12],
                ],
            ];
        }

        return $data;
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
}
