<?php

use App\Livewire\Configurations\Commission\CommissionForm;
use App\Livewire\Configurations\Commission\CommissionTable;
use Livewire\Livewire;
use Modules\HotelContentRepository\Models\Commission;
// use Tests\TestCase;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;
use Illuminate\Foundation\Testing\WithFaker;

// uses(TestCase::class);
// uses(CustomAuthorizedActionsTestCase::class);
uses(WithFaker::class);

beforeEach(function () {
    $this->auth();
});

test('commissions index is opening', function () {
    Commission::factory(10)->create();

    $this->get(route('configurations.commissions.index'))
        ->assertSeeLivewire(CommissionTable::class)
        ->assertStatus(200);

    $component = Livewire::test(CommissionTable::class);

    $commissions = Commission::limit(10)->get(['name']);
    foreach ($commissions as $commission) {
        $component->assertSee([$commission->name]);
    }
});

test('commissions create is opening', function () {
    $this->get(route('configurations.commissions.create'))
        ->assertSeeLivewire(CommissionForm::class)
        ->assertStatus(200);

    $component = Livewire::test(CommissionForm::class, ['commission' => new Commission]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.commissions.index'));

    $this->assertDatabaseHas('pd_commissions', ['name' => $name]);
});
