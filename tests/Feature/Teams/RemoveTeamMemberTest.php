<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('team members can be removed from teams', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $user->currentTeam->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'admin']
    );

    Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->set('teamMemberIdBeingRemoved', $otherUser->id)
        ->call('removeTeamMember');

    $this->assertCount(0, $user->currentTeam->fresh()->users);
});

test('only team owner can remove team members', function () {
    $user = User::factory()->withPersonalTeam()->create();

    $user->currentTeam->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'admin']
    );

    $this->actingAs($otherUser);

    Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->set('teamMemberIdBeingRemoved', $user->id)
        ->call('removeTeamMember')
        ->assertStatus(403);
});