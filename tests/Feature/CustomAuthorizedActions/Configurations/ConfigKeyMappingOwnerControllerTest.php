<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigKeyMappingOwnerControllerTest extends CustomAuthorizedActionsTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_the_index_view()
    {
        $response = $this->get(route('configurations.external-identifiers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.configurations.key-mapping-owners.index');
    }

    #[Test]
    public function it_displays_the_create_view()
    {
        $response = $this->get(route('configurations.external-identifiers.create'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.configurations.key-mapping-owners.form');
        $response->assertViewHas('text');
    }
}
