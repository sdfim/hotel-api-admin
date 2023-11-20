<?php

namespace Tests\Feature\API\Content;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelContentDestinationsTest extends TestCase
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
    public function test_hotel_destination_method_response_true()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/destinations?city=London');

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
    public function test_hotel_destination_with_empty_parameter_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/destinations?city=');

        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_destination_without_parameter_method_response_true()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/destinations');

        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
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
