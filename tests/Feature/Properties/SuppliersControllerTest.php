<?php

namespace Tests\Feature\Properties;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class SuppliersControllerTest extends TestCase
{
    use AuthenticatesUser;
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function test_index(): void
    {
        $this->auth();

        $response = $this->get(route('suppliers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.suppliers.index');
    }

    #[Test]
    public function test_create(): void
    {
        $this->auth();

        $response = $this->get(route('suppliers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.suppliers.create');
    }

    #[Test]
    public function test_store(): void
    {
        $this->auth();

        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->text(190),
        ];

        $response = $this->post(route('suppliers.store'), $data);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', $data);
    }

    #[Test]
    public function test_show(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();

        $response = $this->get(route('suppliers.show', $supplier->id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.suppliers.show');
    }

    #[Test]
    public function test_edit(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();

        $response = $this->get(route('suppliers.edit', $supplier->id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.suppliers.edit');
    }

    #[Test]
    public function test_update(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->text(190),
        ];

        $response = $this->put(route('suppliers.update', $supplier->id), $data);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', $data);
    }

    #[Test]
    public function test_destroy(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();

        $response = $this->delete(route('suppliers.destroy', $supplier->id));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
