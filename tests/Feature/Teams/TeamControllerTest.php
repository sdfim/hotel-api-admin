<?php

namespace Tests\Feature\Teams;

use App\Models\Permission;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\AuthenticatesUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class, AuthenticatesUser::class);

beforeEach(function () {
    $this->auth();
    $this->user = auth()->user();

    $this->team = Team::factory()->create(['user_id' => $this->user->id]);
    $this->user->teams()->attach($this->team->id);

    $viewPermission = Permission::factory()->create(['slug' => 'teams.view']);
    $updatePermission = Permission::factory()->create(['slug' => 'teams.update']);
    $this->user->permissions()->attach([$viewPermission->id, $updatePermission->id]);
});

test('it renders the teams index page', function () {
    $response = $this->get(route('teams.index'));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.teams.index');
});

test('it renders the edit team page', function () {
    $response = $this->get(route('teams.edit', $this->team->id));

    $response->assertStatus(200);
    $response->assertViewIs('dashboard.teams.form');
    $response->assertViewHas('team');
});

test('it switches the team', function () {
    $response = $this->post(route('teams.switch'), ['team_id' => $this->team->id]);

    $response->assertStatus(302);
    $response->assertSessionHas('status', 'Команда успешно переключена');
    $this->assertEquals($this->team->id, Auth::user()->currentTeam->id);
});