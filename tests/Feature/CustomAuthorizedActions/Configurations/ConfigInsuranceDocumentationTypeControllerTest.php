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
    $this->get(route('configurations.insurance-documentation-types.index'))
        ->assertStatus(200)
        ->assertViewIs('dashboard.configurations.insurance_documentation_types.index');
});

test('it displays the create view', function () {
    $this->get(route('configurations.insurance-documentation-types.create'))
        ->assertStatus(200)
        ->assertViewIs('dashboard.configurations.insurance_documentation_types.form')
        ->assertViewHas('configInsuranceDocumentationType')
        ->assertViewHas('text');
});
