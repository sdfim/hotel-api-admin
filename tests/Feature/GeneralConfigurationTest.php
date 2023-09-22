<?php

namespace Tests\Feature;

use App\Models\GeneralConfiguration;
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

		$response = $this->get('/general-configuration');

		$response->assertStatus(200);
	}

	public function testSaveIf()
	{
		$this->auth();

		$data = [
			'time_supplier_requests' => 30,
			'time_reservations_kept' => $this->faker->numberBetween(15, 60),
			'currently_suppliers' => $this->faker->numberBetween(5, 20),
			'time_inspector_retained' => $this->faker->numberBetween(5, 20),
			'star_ratings' => date('Y-m-d H:i:s'),
			'stop_bookings' => date('Y-m-d H:i:s'),
		];

		$response = $this->post('/general-configuration/save', $data);
		$this->assertEquals(1, GeneralConfiguration::count());
		$response->assertRedirect();
		$this->assertEquals(30, GeneralConfiguration::first()->time_supplier_requests);
		$response->assertStatus(302);
		$response->assertRedirect('/');
		$this->assertDatabaseHas('general_configurations',  $data);
	}

	public function testSaveElse()
	{
		$this->auth();
		// Убедимся, что в базе данных нет записей
		GeneralConfiguration::truncate();

		$data = [
			'time_supplier_requests' => 30,
			'time_reservations_kept' => $this->faker->numberBetween(15, 60),
			'currently_suppliers' => $this->faker->numberBetween(5, 20),
			'time_inspector_retained' => $this->faker->numberBetween(5, 20),
			'star_ratings' => date('Y-m-d H:i:s'),
			'stop_bookings' => date('Y-m-d H:i:s'),
		];
		$response = $this->post('/general-configuration/save', $data);
		$this->assertEquals(1, GeneralConfiguration::count());
		$this->assertEquals(30, GeneralConfiguration::first()->time_supplier_requests);
		$response->assertStatus(302);
		$response->assertRedirect('/');
		$this->assertDatabaseHas('general_configurations',  $data);

		$updateData = [
			'time_supplier_requests' => 50,
			'time_reservations_kept' => 60,
			'currently_suppliers' => 70,
			'time_inspector_retained' => 80,
			'star_ratings' => date('Y-m-d H:i:s'),
			'stop_bookings' => date('Y-m-d H:i:s'),
		];

		$response = $this->post('/general-configuration/save', $updateData);
		$this->assertEquals(1, GeneralConfiguration::count());
		$response->assertRedirect('/');
		$this->assertEquals(50, GeneralConfiguration::first()->time_supplier_requests);
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
