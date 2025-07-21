<?php

// use Tests\TestCase;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

// uses(TestCase::class);
// uses(CustomAuthorizedActionsTestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->auth();
});

test('it displays the index view', function () {
    $this->get(route('configurations.external-identifiers.index'))
        ->assertStatus(200)
        ->assertViewIs('dashboard.configurations.key-mapping-owners.index');
});

test('it displays the create view', function () {
    $this->get(route('configurations.external-identifiers.create'))
        ->assertStatus(200)
        ->assertViewIs('dashboard.configurations.key-mapping-owners.form')
        ->assertViewHas('text');
});
