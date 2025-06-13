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
            ->assertStatus(200);

        $component = Livewire::test(UsersTable::class);

        $users = User::limit(10)->get(['name', 'email']);
        foreach ($users as $user) {
            $component->assertSee([$user->name, $user->email]);
        }
    }
}
