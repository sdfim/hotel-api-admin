<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_aborts_request_if_user_does_not_have_required_role()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get('/test-admin');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_aborts_request_if_user_does_not_have_required_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get('/test-admin');
        $response->assertStatus(404);
    }

    /** @test */
   /*  public function it_allows_request_if_user_has_required_role_and_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get('/test-admin');
        $response->assertStatus(200);
        $response->assertSee('Admin Page');
    } */
}
