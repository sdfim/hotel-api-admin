<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_users_can_be_deleted(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(DeleteUserForm::class, ['user' => $user->fresh()])
            ->set('password', 'password')
            ->call('deleteUser');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
