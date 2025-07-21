<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('ice portal property table is opening', function () {
    $this->auth();

    $response = $this->get(route('ice-portal.index'));

    $response->assertStatus(200);
});