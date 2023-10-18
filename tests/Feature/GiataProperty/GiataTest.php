<?php

namespace Tests\Feature\GiataProperty;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GiataTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testIndex(): void
    {
        $this->auth();

        $response = $this->get('/admin/giata');

        $response->assertStatus(200);
    }

    public function auth()
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
