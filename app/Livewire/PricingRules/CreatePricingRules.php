<?php

namespace App\Livewire\PricingRules;

use App\Models\PricingRules;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class CreatePricingRules extends Component implements HasForms
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
                Forms\Components\TextInput::make('supplier_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('property')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('destination')
                    ->required()
                    ->maxLength(191),
                Forms\Components\DateTimePicker::make('travel_date')
                    ->required(),
                Forms\Components\TextInput::make('days')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nights')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('rate_code')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('room_type')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('total_guests')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('room_guests')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('number_rooms')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('meal_plan')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model(PricingRules::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = PricingRules::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
       // print_r('dddddd');die;
        return view('livewire.pricing-rules.create-pricing-rules');
    }
}