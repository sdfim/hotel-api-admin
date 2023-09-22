<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class AuthenticateMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_unauthenticated_user_to_login_page()
    {
        $response = $this->get('/suppliers');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_does_not_redirect_if_request_expects_json()
    {
        $this->auth();
        $response = $this->get('/suppliers');
        $response->assertStatus(200);
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
