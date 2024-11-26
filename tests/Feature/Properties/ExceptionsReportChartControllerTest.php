<?php

namespace Tests\Feature\Properties;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class ExceptionsReportChartControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_exceptions_report_chart_controller_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/exceptions-report-chart');

        $response->assertStatus(200);
    }
}
