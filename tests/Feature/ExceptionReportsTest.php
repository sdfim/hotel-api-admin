<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ExceptionReportsTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function test_example(): void
    {
        $this->auth();

        $response = $this->get('/admin/exceptions-report');

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
