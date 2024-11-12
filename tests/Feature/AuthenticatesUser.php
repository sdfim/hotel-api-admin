<?php

namespace Tests\Feature;

use App\Models\User;

trait AuthenticatesUser
{
    public function auth(): void
    {
        $user = User::factory()->create();
//        $user->roles()->create(['name' => 'admin', 'slug' => 'admin']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
