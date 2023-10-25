<?php

namespace Tests\Feature;

use App\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservation;

class ReservationsTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_reservation_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/reservations');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_showing_an_existing_reservation_record(): void
    {
        $this->auth();
        $channel = Channel::factory()->create();
        $reservations = Reservation::factory()->create(['reservation_contains' => json_encode([]), 'channel_id' => $channel->id]);
        $response = $this->get("/admin/reservations/$reservations->id");
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
