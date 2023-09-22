<?php

namespace Tests\Feature;

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

    public function testShowReservations()
	{
		$this->auth();
		$contain = Contains::factory()->create();
		$channel = Channels::factory()->create();
		$reservations = Reservations::factory()->create(['contains_id' => $contain->id, 'channel_id' => $channel->id]);
		$response = $this->get("/reservations/{$reservations->id}");

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

	public function testEdit(){
		$this->auth();
		$response = $this->get(route('reservations.edit', 2));
		$response->assertStatus(302);
        $response->assertRedirect(route('reservations.index'));
	}

	public function testUpdate(){
		$this->auth();
		$response = $this->put(route('reservations.update', [2]), []);
		$response->assertStatus(302);
        $response->assertRedirect(route('reservations.index'));
	}
}
