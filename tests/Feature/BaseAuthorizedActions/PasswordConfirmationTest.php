<?php

namespace Tests\Feature\BaseAuthorizedActions;

use App\Models\User;
use JsonException;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->actingAs($user)->get('/user/confirm-password');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     * @throws JsonException
     */
    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/user/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();

        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     * @return void
     */
    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/user/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
