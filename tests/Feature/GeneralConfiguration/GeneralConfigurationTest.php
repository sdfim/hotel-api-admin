<?php

namespace Tests\Feature\GeneralConfiguration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;


class GeneralConfigurationTest extends TestCase
{

    use RefreshDatabase;
	use WithFaker;

    public function testIndexGeneralConfiguration()
    {
		$this->auth();

        $response = $this->get('/admin/general-configuration');

        $response->assertStatus(200);
    }

	public function auth()
	{
		$user = User::factory()->create();

		$this->post(route('login'), [
			'email' => $user->email,
			'password' => 'password',
		]);
	}
}