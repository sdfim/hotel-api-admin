<?php

namespace Tests\Feature\Suppliers;

use App\Livewire\Suppliers\UpdateSuppliersForm;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateSuppliersFormTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_an_existing_supplier(): void
    {
        $this->auth();
        $suppliers = Suppliers::factory()->create();
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
