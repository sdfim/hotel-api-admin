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

class CreateRateTierForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
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
                            ->unique(),
                        TextInput::make('max_price')
                            ->label('Max Price')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->unique(),
                        TextInput::make('insurance_rate')
                            ->label('Insurance Rate, %')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                    ])
            ])
            ->statePath('data')
        ->model(InsuranceRateTier::class);
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $record = InsuranceRateTier::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('insurance-rate-tiers.index');
    }

    public function render(): View
    {
        return view('livewire.insurance.rate-tiers.create-rate-tier-form');
    }
}
