<?php

namespace Tests\Feature\API\HotelContentRepository;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductFeeTax;

// // uses(RefreshDatabase::class);

test('index', function () {
    ProductFeeTax::factory()->count(3)->create();
    $response = $this->getJson(route('product-fee-taxes.index'));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_product_fees_and_taxes', 3);
});

test('store', function () {
    $data = ProductFeeTax::factory()->make()->toArray();
    $response = $this->postJson(route('product-fee-taxes.store'), $data);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_fees_and_taxes', $data);
});

test('show', function () {
    $feeTax = ProductFeeTax::factory()->create();
    $response = $this->getJson(route('product-fee-taxes.show', $feeTax->id));
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_fees_and_taxes', $feeTax->toArray());
});

test('update', function () {
    $feeTax = ProductFeeTax::factory()->create();
    $data = ProductFeeTax::factory()->make()->toArray();
    $response = $this->putJson(route('product-fee-taxes.update', $feeTax->id), $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_fees_and_taxes', $data);
});

test('destroy', function () {
    $feeTax = ProductFeeTax::factory()->create();
    $response = $this->deleteJson(route('product-fee-taxes.destroy', $feeTax->id));
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_fees_and_taxes', ['id' => $feeTax->id]);
});