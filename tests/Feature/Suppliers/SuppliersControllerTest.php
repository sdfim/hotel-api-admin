<?php

namespace Tests\Feature\Suppliers;

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

        $response = $this->get('/admin/suppliers');
        $response->assertStatus(200);
    }

    public function testCreate()
    {
        $this->auth();

        $suppliers = Suppliers::factory()->create();

        $response = $this->get(route('suppliers.create', $suppliers->id));
        $response->assertStatus(200);
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
       
        $response->assertSessionHas('success', 'Suppliers created successfully.');
    }

    public function testShow()
    {
        $this->auth();

        $suppliers = Suppliers::factory()->create();

        $response = $this->get(route('suppliers.show', $suppliers->id));
        $response->assertStatus(200);
		$response->assertSee($suppliers->name);
		$response->assertSee($suppliers->description);

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
            'name' => $this->faker->name,
            'description' => $this->faker->word,
        ];

        $response = $this->put(route('suppliers.update', [$suppliers->id]), $newData);
        $response->assertStatus(302);
        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', $newData);
    }

	/*
    public function testDestroy()
    {
        $this->auth();

        $suppliers = Suppliers::factory()->create();

        $response = $this->delete(route('suppliers.destroy', [$suppliers->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('suppliers.index'));
        
        $this->assertDatabaseMissing('suppliers', ['id' => $suppliers->id]);
    }
	*/

    public function auth()
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
