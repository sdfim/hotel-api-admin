<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\ContactInformationDepartments\ConfigContactInformationDepartmentForm;
use App\Livewire\Configurations\ContactInformationDepartments\ContactInformationDepartmentForm;
use App\Livewire\Configurations\ContactInformationDepartments\ContactInformationDepartmentTable;
use App\Models\Configurations\ConfigContactInformationDepartment;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigContactInformationDepartmentTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigContactInformationDepartment::factory(10)->create();

        $this->get(route('configurations.contact-information-departments.index'))
            ->assertSeeLivewire(ContactInformationDepartmentTable::class)
            ->assertStatus(200);

        $component = Livewire::test(ContactInformationDepartmentTable::class);

        $departments = ConfigContactInformationDepartment::limit(10)->get(['name']);
        foreach ($departments as $department) {
            $component->assertSee($department->name);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.contact-information-departments.create'))
            ->assertSeeLivewire(ContactInformationDepartmentForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ContactInformationDepartmentForm::class, ['configContactInformationDepartment' => new ConfigContactInformationDepartment()]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.contact-information-departments.index'));

        $this->assertDatabaseHas('config_contact_information_departments', ['name' => $name]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configContactInformationDepartment = ConfigContactInformationDepartment::factory()->create();

        $this->get(route('configurations.contact-information-departments.edit', $configContactInformationDepartment->id))
            ->assertSeeLivewire(ContactInformationDepartmentForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ContactInformationDepartmentForm::class, ['configContactInformationDepartment' => $configContactInformationDepartment]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.contact-information-departments.index'));

        $this->assertDatabaseHas('config_contact_information_departments', ['name' => $name]);
    }
}
