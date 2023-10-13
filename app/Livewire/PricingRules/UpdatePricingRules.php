<?php

namespace App\Livewire\PricingRules;

use App\Models\Channels;
use App\Models\GiataProperty;
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
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdatePricingRules extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public PricingRules $record;

    public function mount (PricingRules $pricingRules): void
    {
        $this->record = $pricingRules;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form (Form $form): Form
    {
        return $form
            ->schema([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Suppliers::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('channel_id')
                    ->label('Channel')
                    ->options(Channels::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Select::make('property')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                        ->where('name', 'like', "%{$search}%")->limit(30)->pluck('full_name', 'code')->toArray()
                    )
                    ->getOptionLabelUsing(fn($value): ?string => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'))
                        ->where('code', $value)->first()->full_name)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('destination', null);
                        $destination = GiataProperty::select('city')->where('code', $get('property'))->first();
                        $set('destination', $destination->city ?? '');
                    })
                    ->live()
                    ->required()
                    ->unique(ignorable: $this->record),
                TextInput::make('destination')
                    ->readOnly()
                    ->required(),
                DateTimePicker::make('travel_date')
                    ->required(),
                DateTimePicker::make('rule_start_date')
                    ->required(),
                DateTimePicker::make('rule_expiration_date')
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
                        'percentage' => 'Percentage',
                    ])
                    ->live(),
                TextInput::make('price_value_to_apply')
                    ->required()
                    ->numeric(),
                Select::make('price_value_fixed_type_to_apply')
                    ->options([
                        'per_guest' => 'Per Guest',
                        'per_room' => 'Per Room',
                        'per_night' => 'Per Night',
                    ])
                    ->visible(fn(Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
                    ->required(fn(Get $get): bool => $get('price_value_type_to_apply') === 'fixed_value')
            ])
            ->statePath('data')
            ->model($this->record);
    }

    protected function onValidationError (ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    public function edit (): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('pricing_rules.index');
    }

    public function render (): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
