<?php

use App\Livewire\Configurations\ContactInformationDepartments\ContactInformationDepartmentForm;
use App\Livewire\Configurations\ContactInformationDepartments\ContactInformationDepartmentTable;
use App\Models\Configurations\ConfigContactInformationDepartment;
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
    ConfigContactInformationDepartment::factory(10)->create();

    $this->get(route('configurations.contact-information-departments.index'))
        ->assertSeeLivewire(ContactInformationDepartmentTable::class)
        ->assertStatus(200);

    $component = Livewire::test(ContactInformationDepartmentTable::class);

    $departments = ConfigContactInformationDepartment::limit(10)->get(['name']);
    foreach ($departments as $department) {
        $component->assertSee($department->name);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.contact-information-departments.create'))
        ->assertSeeLivewire(ContactInformationDepartmentForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ContactInformationDepartmentForm::class, ['configContactInformationDepartment' => new ConfigContactInformationDepartment]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.contact-information-departments.index'));

    $this->assertDatabaseHas('config_contact_information_departments', ['name' => $name]);
});

test('admin edit is opening', function () {
    $configContactInformationDepartment = ConfigContactInformationDepartment::factory()->create();

    $this->get(route('configurations.contact-information-departments.edit', $configContactInformationDepartment->id))
        ->assertSeeLivewire(ContactInformationDepartmentForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ContactInformationDepartmentForm::class, ['configContactInformationDepartment' => $configContactInformationDepartment]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.contact-information-departments.index'));

    $this->assertDatabaseHas('config_contact_information_departments', ['name' => $name]);
});
