<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Suppliers;
use App\Models\User;

class SuppliersControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    public function testIndex(): void
    {
        $this->auth();

        $response = $this->get('/suppliers');

        $response->assertStatus(200);
    }

    public function testCreate()
    {
        $this->auth();
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->word,
        ];

        $response = $this->post(route('suppliers.store'), $data);
        $response->assertStatus(302);
        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', $data);
    }

    public function testStore()
    {
        $this->auth();
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->word,
        ];

        $response = $this->post(route('suppliers.store'), $data);

        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', $data);
        $response->assertSessionHas('success', 'Pricing rule created successfully.');
    }

    public function testShow()
    {
        $this->auth();
        $suppliers = Suppliers::factory()->create();

        $response = $this->get(route('suppliers.show', $suppliers->id));

        $response->assertStatus(200);
    }

    public function testEdit()
    {
        $this->auth();
        $suppliers = Suppliers::factory()->create();

        $response = $this->get(route('suppliers.edit', $suppliers->id));

        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        $this->auth();
        $suppliers = Suppliers::factory()->create();
        $newData = [
            'name' => 'Updated Suppliers Name',
            'description' => 'Updated Suppliers Description',
        ];

        $response = $this->put(route('suppliers.update', [$suppliers->id]), $newData);
        $response->assertStatus(302);
        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', $newData);
    }

    public function testDestroy()
    {
        $this->auth();
        $suppliers = Suppliers::factory()->create();

        $response = $this->delete(route('suppliers.destroy', [$suppliers->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $suppliers->id]);
    }

    public function auth()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
