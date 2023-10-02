<?php

namespace App\Livewire\PricingRules;

use App\Models\ExpediaContent;
use App\Models\PricingRules;
use App\Models\Suppliers;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
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
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Suppliers::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Select::make('property')
                    ->label('Property')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => ExpediaContent::where('name', 'like', "%{$search}%")->limit(20)->pluck('name', 'property_id')->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => ExpediaContent::find($value)?->name)
                    ->required(),
                Select::make('destination')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => ExpediaContent::where('city', 'like', "%{$search}%")->limit(20)->pluck('city', 'giata_TTIcode')->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => ExpediaContent::find($value)?->city)
                    ->required(),
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
                Select::make('room_type')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => ExpediaContent::where('rooms', 'like', "%{$search}%")->limit(20)->pluck('rooms', 'giata_TTIcode')->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => ExpediaContent::find($value)?->rooms)
                    ->required(),
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
                //total price, net price, rate price
                Select::make('price_type_to_apply')
                    ->required()
                    ->options([
                        'total_price' => 'Guest',
                        'net_price' => 'Per Room',
                        'rate_price' => 'Per Night',
                    ]),
                Select::make('price_value_type_to_apply')
                    ->required()
                    ->options([
                        'fixed' => 'Fixed',
                        'percentage,' => 'Percentage',
                    ]),

                TextInput::make('price_value_to_apply')
                    ->required()
                    ->numeric(),
                //show if fixed to price_value_type_to_apply
                Select::make('price_type_to_apply')
                    ->required()
                    ->options([
                        'guest' => 'Guest',
                        'per_room' => 'Per Room',
                        'per_night' => 'Per Night',
                    ]),
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
        return view('livewire.pricing-rules.create-pricing-rules');
    }
}
