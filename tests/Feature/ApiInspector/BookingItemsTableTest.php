<?php

namespace Tests\Feature\ApiInspector;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('booking items table is opening', function () {
    $this->auth();

    $response = $this->get('/admin/booking-items');

    $response->assertStatus(200);
});