<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Supplier;
use App\Models\User;
use App\Livewire\Suppliers\CreateSuppliersForm;
use Livewire\Livewire;
use App\Livewire\Suppliers\UpdateSuppliersForm;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_validation_of_supplier_form_as_well_as_new_supplier_creating(): void
    {
        $this->auth();
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

        $suppliers = Supplier::factory()->create();

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

        $suppliers = Supplier::factory()->create();

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

        $suppliers = Supplier::factory()->create();
        $response = $this->get(route('suppliers.edit', $suppliers->id));
        $response->assertStatus(200);
    }


    /**
     * @test
     * @return void
     */
    public function test_possibility_of_destroying_an_existing_supplier(): void
    {
        $this->auth();
        $suppliers = Supplier::factory()->create();
        $suppliers->delete();
        $this->assertDatabaseMissing('suppliers', ['id' => $suppliers->id]);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_an_existing_supplier(): void
    {
        $this->auth();
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
