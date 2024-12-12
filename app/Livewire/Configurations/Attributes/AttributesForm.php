<?php

namespace App\Livewire\Configurations\Attributes;

use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class AttributesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigAttribute $record;

    public function mount(ConfigAttribute $configAttribute): void
    {
        $this->record = $configAttribute;

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
        if (!isset($data['default_value'])) {
            $data['default_value'] = '';
        }
        $this->record->fill($data);
        $this->record->save();

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.attributes.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.attributes.attributes-form');
    }
}
