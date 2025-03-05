<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\Suppliers\CreateSuppliersForm;
use App\Livewire\Suppliers\UpdateSuppliersForm;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class SuppliersTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_suppliers_index_is_opening(): void
    {
        $response = $this->get('/admin/suppliers');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_possibility_of_creating_supplier(): void
    {
        $suppliers = Supplier::factory()->create();

        $response = $this->get(route('suppliers.create', $suppliers->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function test_possibility_of_storing_supplier(): void
    {
        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->word(),
        ];

        $response = $this->post(route('suppliers.store'), $data);

        $response->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', $data);

        $response->assertSessionHas('success', 'Suppliers created successfully.');
    }

    #[Test]
    public function test_possibility_of_showing_an_existing_supplier(): void
    {
        $suppliers = Supplier::factory()->create();

        $response = $this->get(route('suppliers.show', $suppliers->id));

        $response->assertStatus(200);

        $response->assertSee($suppliers->name);

        $response->assertSee($suppliers->description);

    }

    #[Test]
    public function test_possibility_of_editing_an_existing_supplier(): void
    {
        $suppliers = Supplier::factory()->create();

        $response = $this->get(route('suppliers.edit', $suppliers->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function test_possibility_of_destroying_an_existing_supplier(): void
    {
        $suppliers = Supplier::factory()->create();

        $suppliers->delete();

        $this->assertDatabaseMissing('suppliers', ['id' => $suppliers->id]);
    }
}
