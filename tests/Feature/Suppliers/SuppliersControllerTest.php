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

    /**
     * @test
     * @return void
     */
    public function test_suppliers_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/suppliers');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_creating_supplier(): void
    {
        $this->auth();

        $suppliers = Suppliers::factory()->create();

        $response = $this->get(route('suppliers.create', $suppliers->id));
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_storing_supplier(): void
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

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_showing_an_existing_supplier(): void
    {
        $this->auth();

        $suppliers = Suppliers::factory()->create();

        $response = $this->get(route('suppliers.show', $suppliers->id));
        $response->assertStatus(200);
        $response->assertSee($suppliers->name);
        $response->assertSee($suppliers->description);

    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_editing_an_existing_supplier(): void
    {
        $this->auth();

        $suppliers = Suppliers::factory()->create();
        $response = $this->get(route('suppliers.edit', $suppliers->id));
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_an_existing_supplier(): void
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


    /**
     * @test
     * @return void
     */
    public function test_possibility_of_destroying_an_existing_supplier(): void
    {
        /*$this->auth();

        $suppliers = Suppliers::factory()->create();

        $response = $this->delete(route('suppliers.destroy', [$suppliers->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', ['id' => $suppliers->id]);*/
        $this->markTestSkipped('Need to fix or remove this test');
    }


    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
