<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\Roles\RolesForm;
use App\Livewire\Roles\RolesTable;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class RolesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        Role::factory(10)->create();
        $this->get(route('roles.index'))
            ->assertSeeLivewire(RolesTable::class)
            ->assertStatus(200);

        $component = Livewire::test(RolesTable::class);

        $roles = Role::limit(10)->get();
        foreach ($roles as $role) {
            $component->assertSee([$role->name, $role->slug]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('roles.create'))
            ->assertSeeLivewire(RolesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(RolesForm::class, ['role' => new Role()]);

        $name = $this->faker->name;
        $slug = $this->faker->slug;

        $component->set('data', [
            'name' => $name,
            'slug' => $slug,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['name' => $name, 'slug' => $slug]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $role = Role::factory()->create();

        $this->get(route('roles.edit', $role->id))
            ->assertSeeLivewire(RolesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(RolesForm::class, ['role' => $role]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
            'slug' => $role->slug,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => $name]);
    }
}
