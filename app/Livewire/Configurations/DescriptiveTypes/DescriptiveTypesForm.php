<?php

namespace App\Livewire\Configurations\DescriptiveTypes;

use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Enums\DescriptiveLocationEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class DescriptiveTypesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigDescriptiveType $record;

    public function mount(ConfigDescriptiveType $configDescriptiveType): void
    {
        $this->record = $configDescriptiveType;

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
                TextInput::make('type')
                    ->required(),
                Select::make('location')
                    ->options(DescriptiveLocationEnum::class)
                    ->enum(DescriptiveLocationEnum::class)
                    ->required(),
            ])
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

        return redirect()->route('configurations.descriptive-types.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.descriptive-types.descriptive-types-form');
    }
}
