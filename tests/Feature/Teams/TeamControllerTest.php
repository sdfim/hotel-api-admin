<?php

namespace Tests\Feature\Teams;

use App\Models\Permission;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class TeamControllerTest extends CustomAuthorizedActionsTestCase
{
    protected $user;

    protected $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = auth()->user();

        $this->team = Team::factory()->create(['user_id' => $this->user->id]);
        $this->user->teams()->attach($this->team->id);

        $viewPermission = Permission::factory()->create(['slug' => 'teams.view']);
        $updatePermission = Permission::factory()->create(['slug' => 'teams.update']);
        $this->user->permissions()->attach([$viewPermission->id, $updatePermission->id]);
    }

    #[Test]
    public function it_renders_the_teams_index_page()
    {
        $response = $this->get(route('teams.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.teams.index');
    }

    #[Test]
    public function it_renders_the_edit_team_page()
    {
        $response = $this->get(route('teams.edit', $this->team->id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.teams.form');
        $response->assertViewHas('team');
    }

    #[Test]
    public function it_switches_the_team()
    {
        $response = $this->post(route('teams.switch'), ['team_id' => $this->team->id]);

        $response->assertStatus(302);
        $response->assertSessionHas('status', 'Команда успешно переключена');
        $this->assertEquals($this->team->id, Auth::user()->currentTeam->id);
    }
}
