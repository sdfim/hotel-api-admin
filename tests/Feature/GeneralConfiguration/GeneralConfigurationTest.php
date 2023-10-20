<?php

namespace Tests\Feature\GeneralConfiguration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;


class GeneralConfigurationTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;

    public function test_general_configuration_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/general-configuration');

        $response->assertStatus(200);
    }

    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
