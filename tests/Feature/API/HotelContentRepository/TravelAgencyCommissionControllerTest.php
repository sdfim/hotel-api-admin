<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;
use PHPUnit\Framework\Attributes\Test;

class TravelAgencyCommissionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        TravelAgencyCommission::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/travel-agency-commissions');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
            ],
            'message',
        ]);
        $this->assertDatabaseCount('pd_travel_agency_commissions', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = TravelAgencyCommission::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/travel-agency-commissions', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['name', 'product_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_travel_agency_commissions', $data);
    }

    #[Test]
    public function test_show()
    {
        $commission = TravelAgencyCommission::factory()->create();
        $response = $this->request()->getJson("api/repo/travel-agency-commissions/{$commission->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_travel_agency_commissions', $commission->toArray());
    }

    #[Test]
    public function test_update()
    {
        $commission = TravelAgencyCommission::factory()->create();
        $data = TravelAgencyCommission::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/travel-agency-commissions/{$commission->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_travel_agency_commissions', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $commission = TravelAgencyCommission::factory()->create();
        $response = $this->request()->deleteJson("api/repo/travel-agency-commissions/{$commission->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_travel_agency_commissions', ['id' => $commission->id]);
    }
}
