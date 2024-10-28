<?php

namespace App\Livewire\Configurations\Chains;

use App\Models\Configurations\ConfigChain;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class ChainsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigChain $record;

    public function mount(ConfigChain $configChain): void
    {
        $this->record = $configChain;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
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

        return redirect()->route('configurations.chains.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.chains.chains-form');
    }
}
