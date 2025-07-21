<?php

use App\Livewire\Configurations\JobDescriptions\JobDescriptionsForm;
use App\Livewire\Configurations\JobDescriptions\JobDescriptionsTable;
use App\Models\Configurations\ConfigJobDescription;
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
    ConfigJobDescription::factory(10)->create();

    $this->get(route('configurations.job-descriptions.index'))
        ->assertSeeLivewire(JobDescriptionsTable::class)
        ->assertStatus(200);

    $component = Livewire::test(JobDescriptionsTable::class);

    $jobDescriptions = ConfigJobDescription::limit(10)->get(['name', 'description']);
    foreach ($jobDescriptions as $jobDescription) {
        $component->assertSee([$jobDescription->name, $jobDescription->description]);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.job-descriptions.create'))
        ->assertSeeLivewire(JobDescriptionsForm::class)
        ->assertStatus(200);

    $component = Livewire::test(JobDescriptionsForm::class, [
        'configJobDescription' => new ConfigJobDescription,
    ]);

    $name = $this->faker->word();
    $description = $this->faker->sentence();

    $component->set('data', [
        'name' => $name,
        'description' => $description,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.job-descriptions.index'));

    $this->assertDatabaseHas('config_job_descriptions', ['name' => $name, 'description' => $description]);
});

test('admin edit is opening', function () {
    $configJobDescription = ConfigJobDescription::factory()->create();

    $this->get(route('configurations.job-descriptions.edit', $configJobDescription->id))
        ->assertSeeLivewire(JobDescriptionsForm::class)
        ->assertStatus(200);

    $component = Livewire::test(JobDescriptionsForm::class, [
        'configJobDescription' => $configJobDescription,
    ]);

    $name = $this->faker->word();
    $description = $this->faker->sentence();

    $component->set('data', [
        'name' => $name,
        'description' => $description,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.job-descriptions.index'));

    $this->assertDatabaseHas('config_job_descriptions', ['name' => $name, 'description' => $description]);
});
