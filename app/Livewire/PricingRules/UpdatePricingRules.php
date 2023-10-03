<?php

namespace App\Livewire\PricingRules;

use App\Models\ExpediaContent;
use App\Models\PricingRules;
use App\Models\Suppliers;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Livewire\Features\SupportRedirects\Redirector;

class UpdatePricingRules extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public PricingRules $record;

    public function mount(PricingRules $pricingRule): void
    {
        $this->record = $pricingRule;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Suppliers::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Select::make('property')
                    ->label('Property')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => ExpediaContent::where('name', 'like', "%{$search}%")->limit(20)->pluck('name', 'property_id')->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => ExpediaContent::find($value)?->name)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('room_type', '');
                        $destination = ExpediaContent::where('property_id', $get('property'))->pluck('city', 'giata_TTIcode')->toArray();
                        $set('destination', ['0' => 'New Delhi']);
                    })
                    ->required(),
                Select::make('destination')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => ExpediaContent::where('city', 'like', "%{$search}%")->limit(20)->pluck('city', 'giata_TTIcode')->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => ExpediaContent::find($value)?->city)
                    ->afterStateUpdated(function (Set $set) {
                        $set('room_type', '');
                    })
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
                    ->options(function (Get $get, Set $set): array {
                       $options = [];
                        if ($get('property')) {
                            $rooms = ExpediaContent::where('property_id', $get('property'))->first(['rooms']);

                            if ($rooms) {
                                foreach ($rooms->rooms as $id => $room) {
                                    $options[$id] = $room['name'];
                                }
                            }
                        }
                        return $options;
                    })
                    ->searchable()
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
                Select::make('price_type_to_apply')
                    ->required()
                    ->options([
                        'total_price' => 'Total Price',
                        'net_price' => 'Net Price',
                        'rate_price' => 'Rate Price',
                    ]),
                Select::make('price_value_type_to_apply')
                    ->required()
                    ->options([
                        'fixed_value' => 'Fixed Value',
                        'percentage,' => 'Percentage',
                    ])
                    ->live(),
                TextInput::make('price_value_to_apply')
                    ->required()
                    ->numeric(),
                Select::make('price_value_fixed_type_to_apply')
                    ->required()
                    ->options([
                        'guest' => 'Guest',
                        'per_room' => 'Per Room',
                        'per_night' => 'Per Night',
                    ])
                    ->visible(fn (Get $get): string => $get('price_value_type_to_apply') === 'fixed_value')
                    ->required(fn (Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();
        return redirect()->route('pricing_rules.index');
    }

    public function render(): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
