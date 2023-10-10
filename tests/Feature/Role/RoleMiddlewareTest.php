<?php

namespace Tests\Feature\Role;

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
   
}
