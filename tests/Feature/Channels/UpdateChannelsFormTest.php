<?php

namespace Tests\Feature\Channels;

use App\Livewire\Channels\UpdateChannelsForm;
use App\Models\Channels;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateChannelsFormTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_validation_of_channel_form_and_updating_an_existing_channel(): void
    {
        $this->auth();
        $channel = Channels::factory()->create();

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
