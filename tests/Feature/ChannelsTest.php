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

    public function testIndex(): void
    {
        $this->auth();

        $response = $this->get('/admin/channels');

        $response->assertStatus(200);
    }

    public function testCreate()
    {
        $this->auth();
        $channels = Channels::factory()->create();

        $response = $this->get(route('channels.create', $channels->id));
        $response->assertStatus(200);
    }

    public function testStore()
    {
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

        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->word,
        ];

        $response = $this->post(route('channels.store'), $data);
        $response->assertRedirect(route('channels.index'));

        $this->assertDatabaseHas('channels', $data);

        $response->assertSessionHas('success', 'Channels created successfully.');
    }

    public function testShow()
    {
        $this->auth();

        $channel = Channels::factory()->create();


        $response = $this->get(route('channels.show', $channel->id));
        $response->assertStatus(200);
        $response->assertSee($channel->name);
        $response->assertSee($channel->description);
    }

    public function testEdit()
    {
        $this->auth();

        $channels = Channels::factory()->create();
        $response = $this->get(route('channels.edit', $channels->id));
        $response->assertStatus(200);
    }

    public function testUpdate()
    {
        $this->auth();

        $channel = Channels::factory()->create();
        $data = [
            'name' => $this->faker->name,
            'description' => $this->faker->word,
        ];

        $response = $this->put("/channels/{$channel->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect('/channels');
        $this->assertDatabaseHas('channels', $data);
    }

    public function testDestroy()
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
