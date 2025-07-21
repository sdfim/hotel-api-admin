<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('property mapping controller is opening', function () {
    $this->auth();

    $response = $this->get('/admin/statistic-charts');

    $response->assertStatus(200);
});