<?php

namespace Tests\Feature\ExpediaContent;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpediaTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_expedia_table_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/expedia');

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
