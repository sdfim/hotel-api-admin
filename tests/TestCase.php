<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabaseMany;

    protected function auth(): void
    {
        $user = User::factory()->create();
        $user->roles()->create(['name' => 'admin', 'slug' => 'admin']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
