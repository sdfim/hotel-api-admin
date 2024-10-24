<?php

namespace App\Livewire\Insurance\RateTiers;

use Filament\Forms\Components\Grid;
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
                Grid::make(3)
                    ->schema([
                        TextInput::make('min_price')
                            ->label('Min Price')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->unique(ignorable: $this->record),
                        TextInput::make('max_price')
                            ->label('Max Price')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->unique(ignorable: $this->record),
                        TextInput::make('insurance_rate')
                            ->label('Insurance Rate, %')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                    ])
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
