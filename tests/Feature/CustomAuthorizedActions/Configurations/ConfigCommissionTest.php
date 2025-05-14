<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\Commission\CommissionForm;
use App\Livewire\Configurations\Commission\CommissionTable;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Modules\HotelContentRepository\Models\Commission;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigCommissionTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_commissions_index_is_opening(): void
    {
        Commission::factory(10)->create();

        $this->get(route('configurations.commissions.index'))
            ->assertSeeLivewire(CommissionTable::class)
            ->assertStatus(200);

        $component = Livewire::test(CommissionTable::class);

        $commissions = Commission::limit(10)->get(['name']);
        foreach ($commissions as $commission) {
            $component->assertSee([$commission->name]);
        }
    }

    #[Test]
    public function test_commissions_create_is_opening(): void
    {
        $this->get(route('configurations.commissions.create'))
            ->assertSeeLivewire(CommissionForm::class)
            ->assertStatus(200);

        $component = Livewire::test(CommissionForm::class, ['commission' => new Commission()]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.commissions.index'));

        $this->assertDatabaseHas('pd_commissions', ['name' => $name]);
    }
}
