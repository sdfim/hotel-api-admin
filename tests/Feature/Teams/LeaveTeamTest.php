<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('users can leave teams', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $user->currentTeam->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'admin']
    );

    $this->actingAs($otherUser);

    Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->call('leaveTeam');

    $this->assertCount(0, $user->currentTeam->fresh()->users);
});

test('team owners cant leave their own team', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->call('leaveTeam')
        ->assertHasErrors(['team']);

    $this->assertNotNull($user->currentTeam->fresh());
});