<?php

namespace Tests\Feature\API\Content;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelContentDetailTest extends TestCase
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
    public function test_hotel_detail_method_response_true()
    {
        $jsonData = $this->hotelSearchRequest();
        $response_search = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);
        $hotel_info = $response_search['data']['results'];
        $hotel_info = $hotel_info['Expedia'][0];
        $hotel_id = $hotel_info['giata_hotel_code'];

        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=' . $hotel_id . '&type=hotel');

        $response_detail
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_false_property_id_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=99999999999999&type=hotel');

        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_without_type_parameter_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=99999999999999');
        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_without_property_id_parameter_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?type=hotel');
        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @return array
     */
    private function hotelSearchRequest(): array
    {
        return [
            'type' => 'hotel',
            'destination' => 1175,
            'rating' => 4,
            'page' => 1,
            'results_per_page' => 250,
        ];
    }

    /**
     * @return void
     */
    private function seederSupplier(): void
    {
        $supplier = Supplier::firstOrNew([
            'name' => 'Expedia',
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
