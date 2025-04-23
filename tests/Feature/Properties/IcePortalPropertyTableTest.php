<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class IcePortalPropertyTableTest extends TestCase
{
    use AuthenticatesUser;
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function test_ice_portal_property_table_is_opening(): void
    {
        $this->auth();

        $response = $this->get(route('ice-portal.index'));

        $response->assertStatus(200);
    }
}
