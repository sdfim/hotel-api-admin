<?php

namespace Feature\API\Booking;

use App\Models\User;

trait HotelBookingGeneralMethodsTrait
{
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
