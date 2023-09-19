<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ReservationsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testListReservations(): void
    {
        $this->auth();

        $response = $this->get('/reservations');

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
