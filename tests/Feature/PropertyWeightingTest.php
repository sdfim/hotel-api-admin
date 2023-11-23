<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Livewire\PropertyWeighting\CreatePropertyWeighting;
use App\Models\Supplier;
use App\Models\User;
use App\Models\PropertyWeighting;
use Livewire\Livewire;
use App\Livewire\PropertyWeighting\UpdatePropertyWeighting;


class PropertyWeightingTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_validation_of_property_weighting_form_during_creation(): void
    {
        $this->auth();

        Livewire::test(CreatePropertyWeighting::class)
            ->set('data', [
                'property' => '',
                'weight' => '',
            ])
            ->call('create')
            ->assertHasErrors([
                'data.property',
                'data.weight',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_property_weighting_form_validation_and_possibility_of_creating_new_property_weighting(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();
        $data = [
            'property' => $this->faker->numberBetween(1, 10000),
            'weight' => 1,
            'supplier_id' => $supplier->id,
        ];

        Livewire::test(CreatePropertyWeighting::class)
            ->set('data', $data)
            ->call('create')
            ->assertRedirect(route('property-weighting.index'));

        $this->assertDatabaseHas('property_weightings', $data);
    }

    /**
     * @test
     * @return void
     */
    public function test_property_weighting_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/property-weighting');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_property_weighting_creating_is_opening(): void
    {
        $this->auth();
        $response = $this->get('/admin/property-weighting/create');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_property_weighting_showing_is_opening(): void
    {
        $this->auth();

        $propertyWeighting = PropertyWeighting::factory()->create();

        $response = $this->get(route('property-weighting.show', $propertyWeighting->id));
        $response->assertStatus(200);
    }
    
    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_an_existing_property_weighting(): void
    {
        $this->auth();
        $property_weighting = PropertyWeighting::factory()->create();
        $supplier = Supplier::factory()->create();

        Livewire::test(UpdatePropertyWeighting::class, ['propertyWeighting' => $property_weighting])
            ->set('data.property',$this->faker->numberBetween(1, 10000))
            ->set('data.weight', 2)
            ->set('data.supplier_id', $supplier->id)
            ->call('edit')
            ->assertRedirect(route('property-weighting.index'));
        $this->assertDatabaseHas('property_weightings', [
            'id' => $property_weighting->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
