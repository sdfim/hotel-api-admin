<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContentLoaderExceptionsControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_content_loader_exceptions_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/content-loader-exceptions');

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
