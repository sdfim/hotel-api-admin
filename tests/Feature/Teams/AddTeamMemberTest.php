<?php

namespace Tests\Feature\Teams;

use App\Actions\Jetstream\AddTeamMember;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('team members can be added', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $team = $user->currentTeam;

    $faker = Faker::create();
    $fakeEmail = $faker->unique()->safeEmail();

    $newMember = User::factory()->create(['email' => $fakeEmail]);

    $action = new AddTeamMember;
    $action->add($user, $team, $fakeEmail, 'viewer');

    $this->assertTrue($team->fresh()->hasUserWithEmail($fakeEmail));
});

test('non-existent team members cannot be added', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $team = $user->currentTeam;

    $action = new AddTeamMember;

    $this->expectException(\Illuminate\Validation\ValidationException::class);

    $action->add($user, $team, 'nonexistent@example.com', 'test-role');

    $this->assertFalse($team->fresh()->hasUserWithEmail('nonexistent@example.com'));
});