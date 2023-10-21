<?php

namespace Tests\Feature\Reservations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Reservations;
use App\Models\Contains;
use App\Models\Channels;


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
        $contain = Contains::factory()->create();
        $channel = Channels::factory()->create();
        $reservations = Reservations::factory()->create(['contains_id' => $contain->id, 'channel_id' => $channel->id]);
        $response = $this->get("/admin/reservations/$reservations->id");

        $response->assertSee($reservations->date_offload);
        $response->assertSee($reservations->date_travel);
        $response->assertSee($reservations->passenger_surname);
        $response->assertSee($reservations->contains_id);
        $response->assertSee($reservations->channel_id);
        $response->assertSee($reservations->total_cost);
        $response->assertSee($reservations->created_at);
        $response->assertSee($reservations->updated_at);
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
