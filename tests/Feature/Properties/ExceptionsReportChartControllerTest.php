<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('exceptions report chart controller is opening', function () {
    $this->auth();

    $response = $this->get('/admin/exceptions-report-chart');

    $response->assertStatus(200);
});