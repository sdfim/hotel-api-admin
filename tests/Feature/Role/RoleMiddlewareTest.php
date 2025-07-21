<?php

namespace Tests\Feature\Role;

use App\Models\User;

test('it aborts request if user does not have required role', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'web');
    $response = $this->get('/test-admin');
    $response->assertStatus(302);
});

test('it aborts request if user does not have required permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'web');
    $response = $this->get('/test-admin');
    $response->assertStatus(302);
});