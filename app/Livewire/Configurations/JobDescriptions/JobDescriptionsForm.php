<?php

namespace App\Livewire\Configurations\JobDescriptions;

use App\Models\Configurations\ConfigJobDescription;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class JobDescriptionsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigJobDescription $record;

    public function mount(ConfigJobDescription $configJobDescription): void
    {
        $this->record = $configJobDescription;

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

        return redirect()->route('configurations.job-descriptions.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.job-descriptions.job-descriptions-form');
    }
}
