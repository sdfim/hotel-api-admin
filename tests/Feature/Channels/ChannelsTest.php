<?php

namespace Tests\Feature\Channels;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChannelsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_channels_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/channels');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_creating_channel(): void
    {
        $this->auth();
        $channels = Channel::factory()->create();

        $response = $this->get(route('channels.create', $channels->id));
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_storing_new_channel(): void
    {
        $this->auth();
        $token = auth()->user()->createToken('New Channel Name');
        $response = $this->post('/admin/channels', [
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => 'New Channel Name',
            'description' => $this->faker->sentence,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/channels');
        $this->assertDatabaseHas('channels', ['name' => 'New Channel Name']); // Check if the data is in the database
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_new_channel(): void
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

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_editing_an_existing_channel(): void
    {
        $this->auth();

        $channels = Channel::factory()->create();
        $response = $this->get(route('channels.edit', $channels->id));
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_showing_an_existing_channel(): void
    {
        $this->auth();

        $channel = Channel::factory()->create();

        $response = $this->get(route('channels.show', $channel->id));
        $response->assertStatus(200);
        $response->assertSee($channel->name);
        $response->assertSee($channel->description);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_destroying_an_existing_channel(): void
    {
        $this->auth();

        $channel = Channel::factory()->create();

        $response = $this->delete("/admin/channels/$channel->id");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/channels');
        $this->assertDatabaseMissing('channels', ['id' => $channel->id]);
    }

    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
