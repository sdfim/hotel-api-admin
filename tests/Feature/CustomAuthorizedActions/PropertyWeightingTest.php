<?php

use App\Livewire\PropertyWeighting\CreatePropertyWeighting;
use App\Livewire\PropertyWeighting\UpdatePropertyWeighting;
use App\Models\PropertyWeighting;
use App\Models\Supplier;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

test('validation of property weighting form during creation', function () {
    Livewire::test(CreatePropertyWeighting::class)
        ->set('data', [
            'property' => '',
            'weight' => '',
        ])
        ->call('create')
        ->assertHasErrors([
            'data.property',
            'data.weight',
        ]);
});

test('property weighting form validation and possibility of creating new property weighting', function () {
    $supplier = Supplier::factory()->create();

    $data = [
        'property' => $this->faker->numberBetween(1, 10000),
        'weight' => 1,
        'supplier_id' => $supplier->id,
    ];

    Livewire::test(CreatePropertyWeighting::class)
        ->set('data', $data)
        ->call('create')
        ->assertRedirect(route('property-weighting.index'));

    $this->assertDatabaseHas('property_weightings', $data);
});

test('property weighting index is opening', function () {
    $this->get('/admin/property-weighting')
        ->assertStatus(200);
});

test('property weighting creating is opening', function () {
    $this->get('/admin/property-weighting/create')
        ->assertStatus(200);
});

test('property weighting showing is opening', function () {
    $propertyWeighting = PropertyWeighting::factory()->create();

    $this->get(route('property-weighting.show', $propertyWeighting->id))
        ->assertStatus(200);
});

test('possibility of updating an existing property weighting', function () {
    $property_weighting = PropertyWeighting::factory()->create();

    $supplier = Supplier::factory()->create();

    Livewire::test(UpdatePropertyWeighting::class, ['propertyWeighting' => $property_weighting])
        ->set('data.property', $this->faker->numberBetween(1, 10000))
        ->set('data.weight', 2)
        ->set('data.supplier_id', $supplier->id)
        ->call('edit')
        ->assertRedirect(route('property-weighting.index'));
    $this->assertDatabaseHas('property_weightings', [
        'id' => $property_weighting->id,
        'supplier_id' => $supplier->id,
    ]);
});
