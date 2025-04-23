<?php

namespace Tests\Feature\Addons;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class InformationalServicesControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_informational_services_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/informational-services');

        $response->assertStatus(200);
    }
}
