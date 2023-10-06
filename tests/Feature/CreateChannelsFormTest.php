<?php

namespace Tests\Feature;

use App\Livewire\Channels\CreateChannelsForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CreateChannelsFormTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCreateChannelsFormValidation()
    {
        $this->auth();
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

    public function auth()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
