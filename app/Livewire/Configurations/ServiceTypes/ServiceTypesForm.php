<?php

namespace App\Livewire\Configurations\ServiceTypes;

use App\Models\Configurations\ConfigServiceType;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class ServiceTypesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigServiceType $record;

    public function mount(ConfigServiceType $configServiceType): void
    {
        $this->record = $configServiceType;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
                TextInput::make('cost')
                    ->numeric(),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $data['cost'] = $data['cost'] ?? 0;
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.service-types.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.service-types.service-types-form');
    }
}
