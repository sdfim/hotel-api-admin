<?php

namespace App\Livewire\Configurations\ContactInformationDepartments;

use App\Models\Configurations\ConfigContactInformationDepartment;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class ContactInformationDepartmentForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigContactInformationDepartment $record;

    public function mount(ConfigContactInformationDepartment $configContactInformationDepartment): void
    {
        $this->record = $configContactInformationDepartment;

        $this->form->fill($this->record->attributesToArray());
    }

    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->unique()
                ->required()
                ->maxLength(191),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema())
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.contact-information-departments.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.contact-information-departments.contact-information-department-form');
    }
}
