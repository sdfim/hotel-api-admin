<?php

namespace Tests\Feature\Insurance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class InsuranceRestrictionsControllerTest  extends TestCase
{
    use RefreshDatabase, WithFaker, AuthenticatesUser;

    #[Test]
    public function test_insurance_restrictions_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/insurance-restrictions');

        $response->assertStatus(200);
    }
}
