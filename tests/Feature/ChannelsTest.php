<?php

namespace Tests\Feature;

use App\Models\Channels;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChannelsTest extends TestCase
{
    use RefreshDatabase; 
    use WithFaker;

	public function testListChannel(): void
    {
		$this->auth();

        $response = $this->get('/admin/channels');

        $response->assertStatus(200);
    }

    public function testCreateChannel()
    {
		$this->auth();

        $response = $this->post('/admin/channels', [
            'name' => 'New Channel Name',
            'description' => $this->faker->sentence,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/channels');
        $this->assertDatabaseHas('channels', ['name' => 'New Channel Name']); // Check if the data is in the database
    }

	public function testShowChannel()
	{
		$this->auth();

		$channel = Channels::factory()->create();

		$response = $this->get("/admin/channels/{$channel->id}");

		$response->assertStatus(200);
		$response->assertSee($channel->name);
		$response->assertSee($channel->description);
	}

    public function testUpdateChannel()
    {
		$this->auth();

        $channel = Channels::factory()->create();

        $response = $this->put("/admin/channels/{$channel->id}", [
            'name' => 'Updated Channel Name',
            'description' => 'Updated Channel Description',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/channels');
        $this->assertDatabaseHas('channels', ['name' => 'Updated Channel Name']);
    }

    public function testDeleteChannel()
    {
		$this->auth();

        $channel = Channels::factory()->create();

        $response = $this->delete("/admin/channels/{$channel->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/channels');
        $this->assertDatabaseMissing('channels', ['id' => $channel->id]);
    }

	public function auth()
	{
		$user = User::factory()->create();

		$this->post('/login', [
			'email' => $user->email,
			'password' => 'password',
		]);
	}
}
