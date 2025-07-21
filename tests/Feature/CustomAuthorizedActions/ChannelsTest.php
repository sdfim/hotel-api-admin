<?php

use App\Livewire\Channels\CreateChannelsForm;
use App\Livewire\Channels\UpdateChannelsForm;
use App\Models\Channel;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\WithFaker;

uses(Illuminate\Foundation\Testing\WithFaker::class);

test('channels index is opening', function () {
    $this->get('/admin/channels')
        ->assertStatus(200);
});

test('possibility of creating channel', function () {
    $channels = Channel::factory()->create();

    $this->get(route('channels.create', $channels->id))
        ->assertStatus(200);
});

test('possibility of storing new channel', function () {
    $token = auth()->user()->createToken('New Channel Name');

    $this->post('/admin/channels', [
        'token_id' => $token->accessToken->id,
        'access_token' => $token->plainTextToken,
        'name' => 'New Channel Name',
        'description' => $this->faker->sentence(),
    ])
        ->assertStatus(302)
        ->assertRedirect('/admin/channels');

    // Check if the data is in the database
    $this->assertDatabaseHas('channels', ['name' => 'New Channel Name']);
});

test('possibility of updating new channel', function () {
    $data = [
        'name' => $this->faker->name(),
        'description' => $this->faker->word(),
    ];

    $this->post(route('channels.store'), $data)
        ->assertRedirect(route('channels.index'))
        ->assertSessionHas('success', 'Channels created successfully.');

    $this->assertDatabaseHas('channels', $data);
});

test('possibility of editing an existing channel', function () {
    $channels = Channel::factory()->create();

    $this->get(route('channels.edit', $channels->id))
        ->assertStatus(200);
});

test('possibility of showing an existing channel', function () {
    $channel = Channel::factory()->create();

    $this->get(route('channels.show', $channel->id))
        ->assertStatus(200)
        ->assertSee($channel->name)
        ->assertSee($channel->description);
});

test('validation of channel form and updating an existing channel', function () {
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
});

test('validation of channel form and storing new channel', function () {
    Livewire::test(CreateChannelsForm::class)
        ->set('data', [
            'name' => '',
            'description' => '',
        ])
        ->call('create')
        ->assertHasErrors(['data.name', 'data.description']);

    $data = [
        'name' => $this->faker->name(),
        'description' => $this->faker->sentence(),
    ];

    Livewire::test(CreateChannelsForm::class)
        ->set('data', $data)
        ->call('create')
        ->assertRedirect(route('channels.index'));

    $this->assertDatabaseHas('channels', $data);

    Livewire::test(CreateChannelsForm::class)
        ->set('data', $data)
        ->call('create')
        ->assertHasErrors(['data.name' => 'The name has already been taken.']);
});

