<?php

namespace Tests\Feature\API\HotelContentRepository;

use Modules\HotelContentRepository\Models\Vendor;

test('can list vendors', function () {
    $initialCount = Vendor::count();
    Vendor::factory()->count(3)->create();
    $response = test()->getJson('api/repo/vendors');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'name', 'address', 'lat', 'lng', 'website',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_vendors', $initialCount + 3);
});

test('can create vendor', function () {
    $data = Vendor::factory()->make()->toArray();
    $response = test()->postJson('api/repo/vendors', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'name', 'address', 'lat', 'lng', 'website',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_vendors', $data);
});

test('can show vendor', function () {
    $vendor = Vendor::factory()->create();
    $response = test()->getJson("api/repo/vendors/{$vendor->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'name', 'address', 'lat', 'lng', 'website',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_vendors', $vendor->toArray());
});

test('can update vendor', function () {
    $vendor = Vendor::factory()->create();
    $data = Vendor::factory()->make(['id' => $vendor->id])->toArray();
    $response = test()->putJson("api/repo/vendors/{$vendor->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name', 'address', 'lat', 'lng', 'website'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_vendors', $data);
});

test('can delete vendor', function () {
    $vendor = Vendor::factory()->create();
    $response = test()->deleteJson("api/repo/vendors/{$vendor->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_vendors', ['id' => $vendor->id]);
});