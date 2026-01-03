<?php

use App\Models\Mapping;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

uses(WithFaker::class, RefreshDatabase::class);

test('can store a new mapping', function () {
    $expedia_id = 123;
    $giata_id = 456;

    $response = $this->post(route('mapping.store'), [
        'expedia_id' => $expedia_id,
        'giata_id' => $giata_id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('mappings', [
        'supplier_id' => $expedia_id,
        'giata_id' => $giata_id,
        'supplier' => MappingSuppliersEnum::Expedia->value,
    ]);
});

test('can update an existing mapping', function () {
    $expedia_id = 123;
    $giata_id = 456;
    $giata_last_id = 789;
    $mapping = Mapping::factory()->create([
        'supplier_id' => $expedia_id,
        'giata_id' => $giata_last_id,
        'supplier' => MappingSuppliersEnum::Expedia->value,
    ]);

    $response = $this->post(route('mapping.store'), [
        'expedia_id' => $expedia_id,
        'giata_id' => $giata_id,
        'giata_last_id' => $giata_last_id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('mappings', [
        'supplier_id' => $expedia_id,
        'giata_id' => $giata_id,
        'supplier' => MappingSuppliersEnum::Expedia->value,
    ]);
});

test('store deletes mapping if giata_id is null', function () {
    $expedia_id = 123;
    $giata_last_id = 789;
    $mapping = Mapping::factory()->create([
        'supplier_id' => $expedia_id,
        'giata_id' => $giata_last_id,
        'supplier' => MappingSuppliersEnum::Expedia->value,
    ]);

    $response = $this->post(route('mapping.store'), [
        'expedia_id' => $expedia_id,
        'giata_last_id' => $giata_last_id,
        'giata_id' => null,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('mappings', [
        'id' => $mapping->id,
    ]);
});
