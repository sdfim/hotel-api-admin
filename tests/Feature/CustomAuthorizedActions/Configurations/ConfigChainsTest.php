<?php

use App\Livewire\Configurations\Chains\ChainsForm;
use App\Livewire\Configurations\Chains\ChainsTable;
use App\Models\Configurations\ConfigChain;
use Livewire\Livewire;
// use Tests\TestCase;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;
use Illuminate\Foundation\Testing\WithFaker;

// uses(TestCase::class);
// uses(CustomAuthorizedActionsTestCase::class);
uses(WithFaker::class);

beforeEach(function () {
    $this->auth();
});

test('admin index is opening', function () {
    ConfigChain::factory(10)->create();

    $this->get(route('configurations.chains.index'))
        ->assertSeeLivewire(ChainsTable::class)
        ->assertStatus(200);

    $component = Livewire::test(ChainsTable::class);

    $chains = ConfigChain::limit(10)->get(['name']);
    foreach ($chains as $chain) {
        $component->assertSee([$chain->name, $chain->default_value]);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.chains.create'))
        ->assertSeeLivewire(ChainsForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ChainsForm::class, ['configChain' => new ConfigChain]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.chains.index'));

    $this->assertDatabaseHas('config_chains', ['name' => $name]);
});

test('admin edit is opening', function () {
    $configChain = ConfigChain::factory()->create();

    $this->get(route('configurations.chains.edit', $configChain->id))
        ->assertSeeLivewire(ChainsForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ChainsForm::class, ['configChain' => $configChain]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.chains.index'));

    $this->assertDatabaseHas('config_chains', ['name' => $name]);
});
