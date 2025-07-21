<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

// uses(RefreshDatabase::class);

test('index', function () {
    TravelAgencyCommission::factory()->count(3)->create();
    $response = test()->getJson('api/repo/travel-agency-commissions');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'product_id', 'commission_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_travel_agency_commissions', 3);
});

test('store', function () {
    $data = TravelAgencyCommission::factory()->make()->toArray();
    $response = test()->postJson('api/repo/travel-agency-commissions', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['product_id', 'commission_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_travel_agency_commissions', $data);
});

test('show', function () {
    $commission = TravelAgencyCommission::factory()->create();
    $response = test()->getJson("api/repo/travel-agency-commissions/{$commission->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'commission_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_travel_agency_commissions', $commission->toArray());
});

test('update', function () {
    $commission = TravelAgencyCommission::factory()->create();
    $data = TravelAgencyCommission::factory()->make()->toArray();
    $response = test()->putJson("api/repo/travel-agency-commissions/{$commission->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'commission_id', 'commission_value', 'commission_value_type', 'date_range_start', 'date_range_end'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_travel_agency_commissions', $data);
});

test('destroy', function () {
    $commission = TravelAgencyCommission::factory()->create();
    $response = test()->deleteJson("api/repo/travel-agency-commissions/{$commission->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_travel_agency_commissions', ['id' => $commission->id]);
});