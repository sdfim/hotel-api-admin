<?php

namespace Tests\Feature\Properties;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('index', function () {
    $this->auth();

    $response = $this->get(route('suppliers.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.suppliers.index');
});

test('create', function () {
    $this->auth();

    $response = $this->get(route('suppliers.create'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.suppliers.create');
});

test('store', function () {
    $this->auth();

    $data = [
        'name' => $this->faker->name(),
        'description' => $this->faker->text(190),
    ];

    $response = $this->post(route('suppliers.store'), $data);

    $response->assertRedirect(route('suppliers.index'));
    $this->assertDatabaseHas('suppliers', $data);
});

test('show', function () {
    $this->auth();

    $supplier = Supplier::factory()->create();

    $response = $this->get(route('suppliers.show', $supplier->id));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.suppliers.show');
});

test('edit', function () {
    $this->auth();

    $supplier = Supplier::factory()->create();

    $response = $this->get(route('suppliers.edit', $supplier->id));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.suppliers.edit');
});

test('update', function () {
    $this->auth();

    $supplier = Supplier::factory()->create();
    $data = [
        'name' => $this->faker->name(),
        'description' => $this->faker->text(190),
    ];

    $response = $this->put(route('suppliers.update', $supplier->id), $data);

    $response->assertRedirect(route('suppliers.index'));
    $this->assertDatabaseHas('suppliers', $data);
});

test('destroy', function () {
    $this->auth();

    $supplier = Supplier::factory()->create();

    $response = $this->delete(route('suppliers.destroy', $supplier->id));

    $response->assertRedirect(route('suppliers.index'));
    $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
});