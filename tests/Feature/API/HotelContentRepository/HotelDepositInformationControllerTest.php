<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelDepositInformation;
use PHPUnit\Framework\Attributes\Test;

class HotelDepositInformationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelDepositInformation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-deposit-information');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'hotel_id', 'days_departure', 'pricing_parameters', 'pricing_value'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_deposit_information', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelDepositInformation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-deposit-information', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'days_departure', 'pricing_parameters', 'pricing_value'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_deposit_information', $data);
    }

    #[Test]
    public function test_show()
    {
        $depositInformation = HotelDepositInformation::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-deposit-information/{$depositInformation->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'days_departure', 'pricing_parameters', 'pricing_value'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_deposit_information', $depositInformation->toArray());
    }

    #[Test]
    public function test_update()
    {
        $depositInformation = HotelDepositInformation::factory()->create();
        $data = HotelDepositInformation::factory()->make(['hotel_id' => $depositInformation->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-deposit-information/{$depositInformation->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'days_departure', 'pricing_parameters', 'pricing_value'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_deposit_information', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $depositInformation = HotelDepositInformation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-deposit-information/{$depositInformation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_deposit_information', ['id' => $depositInformation->id]);
    }
}
