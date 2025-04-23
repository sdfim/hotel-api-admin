<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\JobDescriptions\JobDescriptionsForm;
use App\Livewire\Configurations\JobDescriptions\JobDescriptionsTable;
use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigJobDescriptionsTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigJobDescription::factory(10)->create();

        $this->get(route('configurations.job-descriptions.index'))
            ->assertSeeLivewire(JobDescriptionsTable::class)
            ->assertStatus(200);

        $component = Livewire::test(JobDescriptionsTable::class);

        $jobDescriptions = ConfigJobDescription::limit(10)->get(['name', 'description']);
        foreach ($jobDescriptions as $jobDescription) {
            $component->assertSee([$jobDescription->name, $jobDescription->description]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.job-descriptions.create'))
            ->assertSeeLivewire(JobDescriptionsForm::class)
            ->assertStatus(200);

        $component = Livewire::test(JobDescriptionsForm::class, [
            'configJobDescription' => new ConfigJobDescription(),
        ]);

        $name = $this->faker->word;
        $description = $this->faker->sentence;

        $component->set('data', [
            'name' => $name,
            'description' => $description,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.job-descriptions.index'));

        $this->assertDatabaseHas('config_job_descriptions', ['name' => $name, 'description' => $description]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configJobDescription = ConfigJobDescription::factory()->create();

        $this->get(route('configurations.job-descriptions.edit', $configJobDescription->id))
            ->assertSeeLivewire(JobDescriptionsForm::class)
            ->assertStatus(200);

        $component = Livewire::test(JobDescriptionsForm::class, [
            'configJobDescription' => $configJobDescription,
        ]);

        $name = $this->faker->word;
        $description = $this->faker->sentence;

        $component->set('data', [
            'name' => $name,
            'description' => $description,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.job-descriptions.index'));

        $this->assertDatabaseHas('config_job_descriptions', ['name' => $name, 'description' => $description]);
    }
}
