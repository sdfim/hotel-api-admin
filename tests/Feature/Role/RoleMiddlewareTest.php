<?php

namespace Tests\Feature\Role;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function test_it_aborts_request_if_user_does_not_have_required_role(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get('/test-admin');
        $response->assertStatus(404);
    }

    /**
     * @test
     * @return void
     */
    public function test_it_aborts_request_if_user_does_not_have_required_permission(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');
        $response = $this->get('/test-admin');
        $response->assertStatus(404);
    }

}
