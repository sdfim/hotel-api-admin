<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class StatisticChartsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_property_mapping_controller_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/statistic-charts');

        $response->assertStatus(200);
    }
}
