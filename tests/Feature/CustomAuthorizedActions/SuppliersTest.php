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
    public function test_validation_of_supplier_form_as_well_as_new_supplier_creating(): void
    {
        Livewire::test(CreateSuppliersForm::class)
            ->set('data', [
                'name' => '',
                'description' => '',
            ])
            ->call('create')
            ->assertHasErrors(['data.name', 'data.description']);

        Livewire::test(CreateSuppliersForm::class)
            ->set('data', [
                'name' => 'Test Suppliers',
                'description' => 'Test Description',
            ])
            ->call('create')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Test Suppliers',
            'description' => 'Test Description',
        ]);
    }

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

    #[Test]
    public function test_possibility_of_updating_an_existing_supplier(): void
    {
        $suppliers = Supplier::factory()->create();

        Livewire::test(UpdateSuppliersForm::class, ['suppliers' => $suppliers])
            ->set('data.name', 'Updated Supplier Name')
            ->set('data.description', 'Updated Supplier Description')
            ->call('edit')
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $suppliers->id,
            'name' => 'Updated Supplier Name',
            'description' => 'Updated Supplier Description',
        ]);
    }
}
