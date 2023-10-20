<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ExceptionReportsTest extends TestCase
{
    public function test_example(): void
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
