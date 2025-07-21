<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('users can be deleted', function () {
    $this->actingAs($user = User::factory()->create());

    Livewire::test(DeleteUserForm::class, ['user' => $user->fresh()])
            ->set('password', 'password')
            ->call('deleteUser');

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});