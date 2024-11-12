<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class IceHbsiPropertyTableTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_ice_hbsi_property_table_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/ice-hbsi');

        $response->assertStatus(200);
    }
}
