<?php

namespace Tests\Feature\Insurance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\AuthenticatesUser;
use Tests\TestCase;

class InsuranceProvidersControllerTest  extends TestCase
{
	use RefreshDatabase, WithFaker, AuthenticatesUser;

	#[Test]
	public function test_insurance_providers_index_is_opening(): void
	{
		$this->auth();

		$response = $this->get('/admin/insurance-providers');

		$response->assertStatus(200);
	}
}
