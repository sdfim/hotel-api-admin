<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelFeeTax;
use PHPUnit\Framework\Attributes\Test;

class HotelFeeTaxControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelFeeTax::factory()->count(3)->create();
        $response =  $this->request()->getJson('api/repo/hotel-fee-taxes');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'hotel_id', 'name', 'net_value', 'rack_value', 'tax', 'type']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_fees_and_taxes', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelFeeTax::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-fee-taxes', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'name', 'net_value', 'rack_value', 'tax', 'type'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_fees_and_taxes', $data);
    }

    #[Test]
    public function test_show()
    {
        $feeTax = HotelFeeTax::factory()->create();
        $response =  $this->request()->getJson("api/repo/hotel-fee-taxes/{$feeTax->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'name', 'net_value', 'rack_value', 'tax', 'type'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_fees_and_taxes', $feeTax->toArray());
    }

    #[Test]
    public function test_update()
    {
        $feeTax = HotelFeeTax::factory()->create();
        $data = HotelFeeTax::factory()->make()->toArray();
        $response = $this->request()->putJson('api/repo/hotel-fee-taxes/' . $feeTax->id, $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'name', 'net_value', 'rack_value', 'tax', 'type'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_fees_and_taxes', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $feeTax = HotelFeeTax::factory()->create();
        $response =  $this->request()->deleteJson("api/repo/hotel-fee-taxes/{$feeTax->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_fees_and_taxes', ['id' => $feeTax->id]);
    }
}
