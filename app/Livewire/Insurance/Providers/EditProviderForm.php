<?php

namespace App\Livewire\Insurance\Providers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Insurance\Models\InsuranceProvider;

class EditProviderForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public InsuranceProvider $record;

    public function mount(InsuranceProvider $insuranceProvider): void
    {
        $this->record = $insuranceProvider;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->unique(ignorable: $this->record)
                    ->required()
                    ->maxLength(191),
                TextInput::make('contact_info')
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('insurance-providers.index');
    }

    public function render(): View
    {
        return view('livewire.insurance.providers.edit-provider-form');
    }
}
