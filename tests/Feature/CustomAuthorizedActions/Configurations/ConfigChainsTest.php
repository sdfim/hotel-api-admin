<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\Chains\ChainsForm;
use App\Livewire\Configurations\Chains\ChainsTable;
use App\Models\Configurations\ConfigChain;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigChainsTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigChain::factory(10)->create();

        $this->get(route('configurations.chains.index'))
            ->assertSeeLivewire(ChainsTable::class)
            ->assertStatus(200);

        $component = Livewire::test(ChainsTable::class);

        $chains = ConfigChain::limit(10)->get(['name']);
        foreach ($chains as $chain) {
            $component->assertSee([$chain->name, $chain->default_value]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.chains.create'))
            ->assertSeeLivewire(ChainsForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ChainsForm::class, ['configChain' => new ConfigChain()]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.chains.index'));

        $this->assertDatabaseHas('config_chains', ['name' => $name]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configChain = ConfigChain::factory()->create();

        $this->get(route('configurations.chains.edit', $configChain->id))
            ->assertSeeLivewire(ChainsForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ChainsForm::class, ['configChain' => $configChain]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.chains.index'));

        $this->assertDatabaseHas('config_chains', ['name' => $name]);
    }
}
