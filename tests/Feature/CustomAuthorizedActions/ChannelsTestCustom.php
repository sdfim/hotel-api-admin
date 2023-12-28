<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Channel;
use Livewire\Livewire;
use App\Livewire\Channels\UpdateChannelsForm;
use App\Livewire\Channels\CreateChannelsForm;

class ChannelsTestCustom extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_channels_index_is_opening(): void
    {
        $response = $this->get('/admin/channels');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_creating_channel(): void
    {
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
        $token = auth()->user()->createToken('New Channel Name');

        $response = $this->post('/admin/channels', [
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => 'New Channel Name',
            'description' => $this->faker->sentence,
        ]);

        $response->assertStatus(302);

        $response->assertRedirect('/admin/channels');

        // Check if the data is in the database
        $this->assertDatabaseHas('channels', ['name' => 'New Channel Name']);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_new_channel(): void
    {
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
        $channel = Channel::factory()->create();

        $response = $this->delete("/admin/channels/$channel->id");

        $response->assertStatus(302);

        $response->assertRedirect('/admin/channels');

        $this->assertDatabaseMissing('channels', ['id' => $channel->id]);
    }

    /**
     * @test
     * @return void
     */
    public function test_validation_of_channel_form_and_updating_an_existing_channel(): void
    {
        $channel = Channel::factory()->create();

        Livewire::test(UpdateChannelsForm::class, ['channel' => $channel])
            ->set('data.name', 'Updated Channel Name')
            ->set('data.description', 'Updated Channel Description')
            ->call('edit')
            ->assertRedirect(route('channels.index'));

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'name' => 'Updated Channel Name',
            'description' => 'Updated Channel Description',
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_validation_of_channel_form_and_storing_new_channel(): void
    {
        Livewire::test(CreateChannelsForm::class)
            ->set('data', [
                'name' => '',
                'description' => '',
            ])
            ->call('create')
            ->assertHasErrors(['data.name', 'data.description']);

        Livewire::test(CreateChannelsForm::class)
            ->set('data', [
                'name' => 'Test Channel',
                'description' => 'Test Description',
            ])
            ->call('create')
            ->assertRedirect(route('channels.index'));

        $this->assertDatabaseHas('channels', [
            'name' => 'Test Channel',
            'description' => 'Test Description',
        ]);
    }
}
