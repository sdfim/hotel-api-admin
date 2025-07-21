<?php

namespace Tests\Feature\ApiInspector;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\AuthenticatesUser;

uses(AuthenticatesUser::class, RefreshDatabase::class, WithFaker::class);

test('search index is opening', function () {
    $this->auth();

    $response = $this->get('/admin/search-inspector');

    $response->assertStatus(200);
});