<?php

namespace Tests\Feature\Teams;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Http\Livewire\TeamMemberManager;
use Laravel\Jetstream\Mail\TeamInvitation;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('team members can be invited to team', function () {
    if (! Features::sendsTeamInvitations()) {
        $this->markTestSkipped('Team invitations not enabled.');
    }

    Mail::fake();

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->set('addTeamMemberForm', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ])->call('addTeamMember');

    Mail::assertSent(TeamInvitation::class);

    $this->assertCount(1, $user->currentTeam->fresh()->teamInvitations);
});

test('team member invitations can be cancelled', function () {
    if (! Features::sendsTeamInvitations()) {
        $this->markTestSkipped('Team invitations not enabled.');
    }

    Mail::fake();

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    // Add the team member...
    $component = Livewire::test(TeamMemberManager::class, ['team' => $user->currentTeam])
        ->set('addTeamMemberForm', [
            'email' => 'test@example.com',
            'role' => 'admin',
        ])->call('addTeamMember');

    $invitationId = $user->currentTeam->fresh()->teamInvitations->first()->id;

    // Cancel the team invitation...
    $component->call('cancelTeamInvitation', $invitationId);

    $this->assertCount(0, $user->currentTeam->fresh()->teamInvitations);
});