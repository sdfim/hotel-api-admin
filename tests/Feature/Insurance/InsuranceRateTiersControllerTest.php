<?php

namespace Tests\Feature\Insurance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class InsuranceRateTiersControllerTest extends TestCase
{
    use AuthenticatesUser;
    use RefreshDatabase;
    use WithFaker;

    #[Test]
    public function test_insurance_rate_tiers_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/insurance-rate-tiers');

        $response->assertStatus(200);
    }
}
