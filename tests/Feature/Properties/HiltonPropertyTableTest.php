<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('hilton property table is opening', function () {
    $this->auth();

    $response = $this->get(route('hilton.index'));

    $response->assertStatus(200);
});

