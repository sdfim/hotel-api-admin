<?php

namespace App\Livewire\PricingRules;

use App\Models\Channel;
use App\Models\GiataProperty;
use App\Models\PricingRule;
use App\Models\Supplier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdatePricingRule extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array|null
     */
    public ?array $data = [];

    /**
     * @var PricingRule
     */
    public PricingRule $record;

    /**
     * @param PricingRule $pricingRule
     * @return void
     */
    public function mount(PricingRule $pricingRule): void
    {
        $this->record = $pricingRule;
        $this->form->fill($this->record->attributesToArray());
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 1,
                'md' => 2,
                'xl' => 3,
                '2xl' => 3,
            ])
            ->schema([
                Fieldset::make('General settings')
                    ->schema([
                        TextInput::make('name')
                            ->label('Rule name')
                            ->maxLength(191)
                            ->unique(ignorable: $this->record)
                            ->required(),
                        DateTimePicker::make('rule_start_date')
                            ->native(false)
                            ->time(false)
                            ->format('Y-m-d')
                            ->displayFormat('d-m-Y')
                            ->required(),
                        DateTimePicker::make('rule_expiration_date')
                            ->native(false)
                            ->time(false)
                            ->format('Y-m-d')
                            ->displayFormat('d-m-Y')
                            ->required()
                    ])
                    ->columns(3),
                Fieldset::make('Price settings')
                    ->schema([
                        Select::make('manipulable_price_type')
                            ->label('Manipulable price type')
                            ->options([
                                'total_price' => 'Total Price',
                                'net_price' => 'Net Price',
                                'rate_price' => 'Rate Price',
                            ])
                            ->required(),
                        TextInput::make('price_value')
                            ->label('Price value')
                            ->numeric()
                            ->required()
                            ->suffixIcon(function (Get $get) {
                                return match ($get('price_value_type')) {
                                    null, '' => false,
                                    'fixed_value' => 'heroicon-o-banknotes',
                                    'percentage' => 'heroicon-o-receipt-percent',
                                };
                            }),
                        Select::make('price_value_type')
                            ->label('Price value type')
                            ->options([
                                'fixed_value' => 'Fixed Value',
                                'percentage' => 'Percentage',
                            ])
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn(?string $state, Set $set) => $state ?: $set('price_value', null)),
                        Select::make('price_value_target')
                            ->label('Price value target')
                            ->options([
                                'per_guest' => 'Per Guest',
                                'per_room' => 'Per Room',
                                'per_night' => 'Per Night',
                            ])
                            ->required()
                    ])
                    ->columns(4),
                Fieldset::make('Rule conditions')
                    ->schema([
                        Repeater::make('conditions')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Select::make('field')
                                    ->options([
                                        'supplier_id' => 'Supplier ID',
                                        'channel_id' => 'Channel ID',
                                        'property' => 'Property',
                                        'destination' => 'Destination',
                                        'travel_date' => 'Travel date',
                                        'booking_date' => 'Booking date',
                                        'total_guests' => 'Total guests',
                                        'days_until_departure' => 'Days until departure',
                                        'nights' => 'Nights',
                                        'rating' => 'Rating',
                                        'number_of_rooms' => 'Number of rooms',
                                        'rate_code' => 'Rate code',
                                        'room_type' => 'Room type',
                                        'meal_plan' => 'Meal plan / Board basis'
                                    ])
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(fn(Select $component) => $component
                                        ->getContainer()
                                        ->getComponent('dynamicFieldValue')
                                        ->getChildComponentContainer()
                                        ->fill()
                                    ),
                                Select::make('compare')
                                    ->options(fn(Get $get): array => match ($get('field')) {
                                        'supplier_id', 'channel_id', 'property', 'destination', 'rate_code', 'room_type', 'meal_plan' => [
                                            '=' => '=',
                                        ],
                                        default => [
                                            '=' => '=',
                                            '<' => '<',
                                            '>' => '>',
                                            'between' => 'between',
                                        ],
                                    })
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(fn(?string $state, Set $set) => $state === 'between' ?: $set('value_to', null)),
                                Grid::make()
                                    ->schema(fn(Get $get): array => match ($get('field')) {
                                        'supplier_id' => [
                                            Select::make('value_from')
                                                ->label('Supplier ID')
                                                ->options(Supplier::all()->pluck('name', 'id'))
                                                ->required()
                                        ],
                                        'channel_id' => [
                                            Select::make('value_from')
                                                ->label('Channel ID')
                                                ->options(Channel::all()->pluck('name', 'id'))
                                                ->required()
                                        ],
                                        'property' => [
                                            Select::make('value_from')
                                                ->label('Property')
                                                ->searchable()
                                                ->getSearchResultsUsing(function (string $search): ?array {
                                                    $result = GiataProperty::select(
                                                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                                                        ->where('name', 'like', "%$search%")->limit(30);
                                                    return $result->pluck('full_name', 'code')->toArray() ?? [];
                                                })
                                                ->getOptionLabelUsing(function ($value): ?string {
                                                    $result = GiataProperty::select(
                                                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'))
                                                        ->where('code', $value)->first();

                                                    return $result->full_name ?? null;
                                                })
                                                ->required()
                                        ],
                                        'destination' => [
                                            Select::make('value_from')
                                                ->label('Destination')
                                                ->searchable()
                                                ->getSearchResultsUsing(function (string $search): array {
                                                    $result = GiataProperty::select(
                                                        DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'), 'city_id')
                                                        ->where('city', 'like', "%$search%")->limit(30);
                                                    return $result->pluck('full_name', 'city_id')->toArray() ?? [];
                                                })
                                                ->getOptionLabelUsing(function ($value): ?string {
                                                    $result = GiataProperty::select(
                                                        DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'))
                                                        ->where('city_id', $value)->first();
                                                    return $result->full_name ?? '';
                                                })
                                                ->required()
                                        ],
                                        'travel_date' => [
                                            DateTimePicker::make('value_from')
                                                ->label('Travel date from')
                                                ->native(false)
                                                ->time(false)
                                                ->format('Y-m-d')
                                                ->displayFormat('d-m-Y')
                                                ->required(),
                                            DateTimePicker::make('value_to')
                                                ->label('Travel date to')
                                                ->native(false)
                                                ->time(false)
                                                ->format('Y-m-d')
                                                ->displayFormat('d-m-Y')
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'booking_date' => [
                                            DateTimePicker::make('value_from')
                                                ->label('Booking date from')
                                                ->native(false)
                                                ->time(false)
                                                ->format('Y-m-d')
                                                ->displayFormat('d-m-Y')
                                                ->required(),
                                            DateTimePicker::make('value_to')
                                                ->label('Booking date to')
                                                ->native(false)
                                                ->time(false)
                                                ->format('Y-m-d')
                                                ->displayFormat('d-m-Y')
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'total_guests' => [
                                            TextInput::make('value_from')
                                                ->label('Total guests from')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('value_to')
                                                ->label('Total guests to')
                                                ->numeric()
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'days_until_departure' => [
                                            TextInput::make('value_from')
                                                ->label('Days until departure from')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('value_to')
                                                ->label('Days until departure to')
                                                ->numeric()
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'nights' => [
                                            TextInput::make('value_from')
                                                ->label('Nights from')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('value_to')
                                                ->label('Nights to')
                                                ->numeric()
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'rating' => [
                                            TextInput::make('value_from')
                                                ->label('Rating from')
                                                ->numeric()
                                                ->minValue(fn(): float => 1.0)
                                                ->maxValue(fn(): float => 5.5)
                                                ->required(),
                                            TextInput::make('value_to')
                                                ->label('Rating to')
                                                ->numeric()
                                                ->minValue(fn(): float => 1.0)
                                                ->maxValue(fn(): float => 5.5)
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'number_of_rooms' => [
                                            TextInput::make('value_from')
                                                ->label('Number of rooms from')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('value_to')
                                                ->label('Number of rooms to')
                                                ->numeric()
                                                ->required(fn(Get $get): bool => $get('compare') === 'between')
                                                ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                                ->visible(fn(Get $get): bool => $get('compare') === 'between')
                                        ],
                                        'rate_code' => [
                                            TextInput::make('value_from')
                                                ->label('Rate code from')
                                                ->maxLength(191)
                                                ->required()
                                        ],
                                        'room_type' => [
                                            TextInput::make('value_from')
                                                ->label('Room type from')
                                                ->maxLength(191)
                                                ->required()
                                        ],
                                        'meal_plan' => [
                                            TextInput::make('value_from')
                                                ->label('Meal plan from')
                                                ->maxLength(191)
                                                ->required()
                                        ],
                                        default => []
                                    })
                                    ->columns()
                                    ->columnStart(3)
                                    ->key('dynamicFieldValue')
                            ])
                            ->required()
                            ->columns(4)
                    ])
                    ->columns(1)
            ])
            ->statePath('data')
            ->model($this->record);
    }

    /**
     * @param ValidationException $exception
     * @return void
     */
    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function edit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('pricing-rules.index');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.pricing-rules.update-pricing-rules');
    }
}
