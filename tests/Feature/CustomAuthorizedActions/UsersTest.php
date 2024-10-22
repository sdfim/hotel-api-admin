<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\Users\UsersForm;
use App\Livewire\Users\UsersTable;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class UsersTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        User::factory(10)->create();

        $this->get(route('users.index'))
            ->assertSeeLivewire(UsersTable::class)
            ->assertStatus(200);

        $component = Livewire::test(UsersTable::class);

        $users = User::limit(10)->get(['name', 'email']);
        foreach ($users as $user) {
            $component->assertSee([$user->name, $user->email]);
        }
    }

    #[Test]
    public function test_admin_edit_is_opening()
    {
        $user = User::factory()->create();

        $this->get(route('users.edit', $user->id))
            ->assertSeeLivewire(UsersForm::class)
            ->assertStatus(200);

        $component = Livewire::test(UsersForm::class, ['user' => $user]);

        $name = $this->faker->name;
        $email = $this->faker->email;

        $component->set('data', [
            'name' => $name,
            'email' => $email,
            'role' => Role::first()->id,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $name,
            'email' => $email,
        ]);
    }
}
