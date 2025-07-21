<?php

use App\Models\Supplier;
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

test('suppliers index is opening', function () {
    $this->get('/admin/suppliers')
        ->assertStatus(200);
});

test('possibility of creating supplier', function () {
    $suppliers = Supplier::factory()->create();

    $this->get(route('suppliers.create', $suppliers->id))
        ->assertStatus(200);
});

test('possibility of storing supplier', function () {
    $data = [
        'name' => $this->faker->name(),
        'description' => $this->faker->word(),
    ];

    $this->post(route('suppliers.store'), $data)
        ->assertRedirect(route('suppliers.index'))
        ->assertSessionHas('success', 'Suppliers created successfully.');

    $this->assertDatabaseHas('suppliers', $data);
});

test('possibility of showing an existing supplier', function () {
    $suppliers = Supplier::factory()->create();

    $this->get(route('suppliers.show', $suppliers->id))
        ->assertStatus(200)
        ->assertSee($suppliers->name)
        ->assertSee($suppliers->description);
});

test('possibility of editing an existing supplier', function () {
    $suppliers = Supplier::factory()->create();

    $this->get(route('suppliers.edit', $suppliers->id))
        ->assertStatus(200);
});

test('possibility of destroying an existing supplier', function () {
    $suppliers = Supplier::factory()->create();

    $suppliers->delete();

    $this->assertDatabaseMissing('suppliers', ['id' => $suppliers->id]);
});
