<?php

namespace App\Livewire\Insurance\RateTiers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Insurance\Models\InsuranceRateTier;

class EditRateTierForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public InsuranceRateTier $record;

    public function mount(InsuranceRateTier $insuranceRateTier): void
    {
        $this->record = $insuranceRateTier;
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

        return redirect()->route('insurance-rate-tiers.index');
    }

    public function render(): View
    {
        return view('livewire.insurance.rate-tiers.edit-rate-tier-form');
    }
}
