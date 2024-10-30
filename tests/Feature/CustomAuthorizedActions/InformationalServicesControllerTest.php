<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\InformationalServices\InformationalServicesForm;
use App\Livewire\InformationalServices\InformationalServicesTable;
use App\Models\InformationalService;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class InformationalServicesControllerTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_index_is_opening(): void
    {
        InformationalService::factory(10)->create();
        $this->get(route('informational-services.index'))
            ->assertSeeLivewire(InformationalServicesTable::class)
            ->assertStatus(200);

        $component = Livewire::test(InformationalServicesTable::class);

        $services = InformationalService::limit(10)->get();
        foreach ($services as $service) {
            $component->assertSee([$service->name, $service->description]);
        }
    }

    #[Test]
    public function test_create_is_opening(): void
    {
        $this->get(route('informational-services.create'))
            ->assertSeeLivewire(InformationalServicesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(InformationalServicesForm::class, ['service' => new InformationalService()]);

        $data = InformationalService::factory()->make()->toArray();

        $component->set('data', $data);

        $component->call('edit');
        $component->assertRedirect(route('informational-services.index'));

        $this->assertDatabaseHas('informational_services', $data);
    }

    #[Test]
    public function test_edit_is_opening(): void
    {
        $service = InformationalService::factory()->create();

        $this->get(route('informational-services.edit', $service->id))
            ->assertSeeLivewire(InformationalServicesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(InformationalServicesForm::class, ['service' => $service]);

        $data = InformationalService::factory()->make()->toArray();

        $component->set('data', $data);

        $component->call('edit');
        $component->assertRedirect(route('informational-services.index'));

        $this->assertDatabaseHas('informational_services', ['id' => $service->id] + $data);
    }
}
