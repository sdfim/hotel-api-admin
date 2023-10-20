<?php

namespace Tests\Feature\Suppliers;

use App\Livewire\Suppliers\CreateSuppliersForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CreateSuppliersFormTest extends TestCase
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
