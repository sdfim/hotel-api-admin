<?php

namespace App\Livewire\PricingRules;

use App\Models\PricingRules;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class UpdatePricingRules extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public PricingRules $record;

    public function mount (PricingRules $pricingRule): void
    {
        $this->record = $pricingRule;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form (Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('supplier_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('property')
                    ->required()
                    ->maxLength(191),
                TextInput::make('destination')
                    ->required()
                    ->maxLength(191),
                DateTimePicker::make('travel_date')
                    ->required(),
                TextInput::make('days')
                    ->required()
                    ->numeric(),
                TextInput::make('nights')
                    ->required()
                    ->numeric(),
                TextInput::make('rate_code')
                    ->required()
                    ->maxLength(191),
                TextInput::make('room_type')
                    ->required()
                    ->maxLength(191),
                TextInput::make('total_guests')
                    ->required()
                    ->numeric(),
                TextInput::make('room_guests')
                    ->required()
                    ->numeric(),
                TextInput::make('number_rooms')
                    ->required()
                    ->numeric(),
                TextInput::make('meal_plan')
                    ->required()
                    ->maxLength(191),
                TextInput::make('rating')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit (): void
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();
    }

    public function render (): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
