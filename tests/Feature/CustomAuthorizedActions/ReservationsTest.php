<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Models\Channel;
use App\Models\Reservation;

class ReservationsTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_reservation_index_is_opening(): void
    {
        $response = $this->get('/admin/reservations');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_possibility_of_showing_an_existing_reservation_record(): void
    {
        $channel = Channel::factory()->create();

        $reservations = Reservation::factory()->create([
            'channel_id' => $channel->id,
        ]);

        $response = $this->get("/admin/reservations/$reservations->id");

        $response->assertStatus(200);
    }
}
