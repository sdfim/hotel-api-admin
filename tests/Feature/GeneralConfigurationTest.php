<?php

namespace Tests\Feature;

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

    public function testSaveGeneralConfiguration()
    {
		$this->auth();

		$data = [
            'time_supplier_requests' => $this->faker->numberBetween(30, 120),
			'time_reservations_kept' => $this->faker->numberBetween(15, 60),
			'currently_suppliers' => $this->faker->numberBetween(5, 20),
			'time_inspector_retained' => $this->faker->numberBetween(5, 20),
			'star_ratings' => date('Y-m-d H:i:s'),
			'stop_bookings' => date('Y-m-d H:i:s'),
        ];

        $response = $this->post('/admin/general-configuration/save', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertDatabaseHas('general_configurations',  $data);     
	}

	public function auth()
	{
		$user = User::factory()->create();

		$this->post('/login', [
			'email' => $user->email,
			'password' => 'password',
		]);
	}
}