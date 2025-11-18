<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Livewire\Components\CustomRepeater;
use App\Models\Channel;
use App\Models\Property;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\Enums\ProductManipulablePriceTypeEnum;
use Modules\Enums\ProductPriceValueTypeEnum;

trait DepositFieldTrait
{
    public function schemeForm($record = null, bool $isDepositForm = false): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),

            Fieldset::make('General Setting')
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->columnSpan(2)
                        ->maxLength(191)
                        ->rules([
                            function () use ($isDepositForm, $record) {
                                $tableName = $isDepositForm ? 'pd_product_deposit_information' : 'pd_product_cancellation_policies';

                                return \Illuminate\Validation\Rule::unique($tableName, 'name')
                                    ->where('product_id', $this->productId)
                                    ->ignore($record);
                            },
                        ])
                        ->required(),
                    DateTimePicker::make('start_date')
                        ->label('Valid From')
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('m/d/Y')
                        ->default(Carbon::now()->format('Y-m-d'))
                        ->required(),

                    DateTimePicker::make('expiration_date')
                        ->label('Valid To')
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('m/d/Y'),

                    Select::make('manipulable_price_type')
                        ->label('Manipulable Price Type')
                        ->options([
                            ProductManipulablePriceTypeEnum::TOTAL_PRICE->value => 'Total Price',
                            ProductManipulablePriceTypeEnum::NET_PRICE->value => 'Net Price',
                        ])
                        ->required(),
                    TextInput::make('price_value')
                        ->label('Price Value')
                        ->numeric()
                        ->required()
                        ->suffixIcon(function (Get $get) {
                            return match ($get('price_value_type')) {
                                null, '' => false,
                                ProductPriceValueTypeEnum::FIXED_VALUE->value => 'heroicon-o-banknotes',
                                ProductPriceValueTypeEnum::PERCENTAGE->value => 'heroicon-o-receipt-percent',
                            };
                        }),
                    Select::make('price_value_type')
                        ->label('Price Value Type')
                        ->options([
                            ProductPriceValueTypeEnum::FIXED_VALUE->value => 'Fixed Value',
                            ProductPriceValueTypeEnum::PERCENTAGE->value => 'Percentage',
                        ])
                        ->live()
                        ->required()
                        ->afterStateUpdated(fn (?string $state, Set $set) => $state ?: $set('price_value', null)),
                    Select::make('price_value_target')
                        ->label('Price Value Target')
                        ->options([
                            ProductApplyTypeEnum::PER_ROOM->value => 'Per Room',
                            ProductApplyTypeEnum::PER_PERSON->value => 'Per Person',
                            ProductApplyTypeEnum::PER_NIGHT->value => 'Per Night',
                            ProductApplyTypeEnum::PER_NIGHT_PER_PERSON->value => 'Per Night Per Person',
                        ])
                        ->required(),
                ])
                ->columns(4),

            Fieldset::make('Initial Payment')
                ->schema([
                    Select::make('initial_payment_due_type')
                        ->label('Initial Payment Due Type')
                        ->inlineLabel()
                        ->options([
                            'days_after_booking' => 'Days After Booking',
                            'days_before_arrival' => 'Days Before Arrival',
                            'date' => 'Date',
                        ])
                        ->live(),
                    TextInput::make('days_after_booking_initial_payment_due')
                        ->label('Days')
                        ->inlineLabel()
                        ->columnSpan(1)
                        ->maxLength(191)
                        ->numeric()
                        ->minValue(0)
                        ->required(fn (Get $get): bool => $get('initial_payment_due_type') === 'days_after_booking')
                        ->visible(fn (Get $get): bool => $get('initial_payment_due_type') === 'days_after_booking'),
                    TextInput::make('days_before_arrival_initial_payment_due')
                        ->label('Days')
                        ->inlineLabel()
                        ->columnSpan(1)
                        ->maxLength(191)
                        ->numeric()
                        ->minValue(1)
                        ->required(fn (Get $get): bool => $get('initial_payment_due_type') === 'days_before_arrival')
                        ->visible(fn (Get $get): bool => $get('initial_payment_due_type') === 'days_before_arrival'),
                    DateTimePicker::make('date_initial_payment_due')
                        ->label('Date')
                        ->inlineLabel()
                        ->columnSpan(1)
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('d-m-Y')
                        ->required(fn (Get $get): bool => $get('initial_payment_due_type') === 'date')
                        ->visible(fn (Get $get): bool => $get('initial_payment_due_type') === 'date'),

                ])
                ->columns(2)
                ->visible($isDepositForm),

            Fieldset::make('Balance Payment')
                ->schema([
                    Select::make('balance_payment_due_type')
                        ->label('Balance Payment Due Type')
                        ->inlineLabel()
                        ->options([
                            'days_after_booking' => 'Days After Booking',
                            'days_before_arrival' => 'Days Before Arrival',
                            'date' => 'Date',
                        ])
                        ->live(),
                    TextInput::make('days_after_booking_balance_payment_due')
                        ->label('Days')
                        ->inlineLabel()
                        ->columnSpan(1)
                        ->maxLength(191)
                        ->numeric()
                        ->minValue(0)
                        ->required(fn (Get $get): bool => $get('balance_payment_due_type') === 'days_after_booking')
                        ->visible(fn (Get $get): bool => $get('balance_payment_due_type') === 'days_after_booking'),
                    TextInput::make('days_before_arrival_balance_payment_due')
                        ->label('Days')
                        ->inlineLabel()
                        ->columnSpan(1)
                        ->maxLength(191)
                        ->numeric()
                        ->minValue(1)
                        ->required(fn (Get $get): bool => $get('balance_payment_due_type') === 'days_before_arrival')
                        ->visible(fn (Get $get): bool => $get('balance_payment_due_type') === 'days_before_arrival'),
                    DateTimePicker::make('date_balance_payment_due')
                        ->label('Date')
                        ->inlineLabel()
                        ->columnSpan(1)
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('d-m-Y')
                        ->required(fn (Get $get): bool => $get('balance_payment_due_type') === 'date')
                        ->visible(fn (Get $get): bool => $get('balance_payment_due_type') === 'date'),

                ])
                ->columns(2)
                ->visible($isDepositForm),

            Fieldset::make('Сonditions')
                ->schema([
                    $this->getBaseRepiter(),
                ])
                ->columns(1),
        ];
    }

    private function getBaseRepiter(): Repeater
    {
        return CustomRepeater::make('conditions')
//            ->relationship()
            ->label('')
            ->addActionLabel($customButtonLabel ?? 'Add condition')
            ->schema([
                Select::make('field')
                    ->options(function () {
                        $options = [
                            'general' => [
                                'supplier_id' => 'Supplier ID',
                                'channel_id' => 'Channel ID',
                                'total_price' => 'Total Price',
                            ],
                            'location' => [
                                'destination' => 'Destination',
                                'room_name' => 'Room name',
                                'room_type' => 'Room type',
                            ],
                            'dates' => [
                                'travel_date' => 'Travel date',
                                'booking_date' => 'Booking date',
                                'date_of_stay' => 'Date of stay',
                            ],
                            'addition' => [
                                'total_guests' => 'Total guests',
                                'days_until_departure' => 'Days until departure',
                                'nights' => 'Nights',
                                'rating' => 'Rating',
                                'number_of_rooms' => 'Number of rooms',
                                'meal_plan' => 'Meal plan / Board basis',
                            ],
                        ];

                        return $options;
                    })
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn (Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicFieldValue')
                        ->getChildComponentContainer()
                        ->fill()
                    ),
                Select::make('compare')
                    ->options(fn (Get $get): array => match ($get('field')) {
                        'supplier_id', 'channel_id', 'meal_plan' => [
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                        ],
                        'destination', 'room_type', 'room_code', 'room_name', 'rate_code' => [
                            'in' => 'In List',
                            '!in' => 'Not In List',
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                        ],
                        default => [
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                            '<' => '< (Less Than)',
                            '>' => '> (Greater Than)',
                            'between' => 'Between',
                        ],
                    })
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (?string $state, Set $set) {
                        $set('value_from', null);
                        $set('value_to', null);
                    }),
                Grid::make()
                    ->schema(components: fn (Get $get): array => match ($get('field')) {
                        'supplier_id' => [
                            Select::make('value_from')
                                ->label('Supplier ID')
                                ->options(Supplier::all()->pluck('name', 'id'))
                                ->required(),
                        ],
                        'channel_id' => [
                            Select::make('value_from')
                                ->label('Channel ID')
                                ->options(Channel::all()->pluck('name', 'id'))
                                ->required(),
                        ],
                        'destination' => [
                            Select::make('value')
                                ->label('Destination')
                                ->searchable()
                                ->multiple()
                                ->getSearchResultsUsing(function (string $search): array {
                                    $result = Property::select(
                                        DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'), 'city_id')
                                        ->where('city', 'like', "%$search%")
                                        ->orWhere('city_id', 'like', "%$search%")
                                        ->limit(30);

                                    return $result->pluck('full_name', 'city_id')->toArray() ?? [];
                                })
                                ->getOptionLabelsUsing(function (array $values): ?array {
                                    $properties = Property::select(DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'), 'city_id')
                                        ->whereIn('city_id', $values)
                                        ->get()
                                        ->mapWithKeys(function ($property) {
                                            return [$property->city_id => $property->full_name];
                                        })
                                        ->toArray();

                                    return $properties;
                                })
                                ->native(false)
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', '!in'])),

                            Select::make('value_from')
                                ->label('Destination')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search): array {
                                    $result = Property::select(
                                        DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'), 'city_id')
                                        ->where('city', 'like', "%$search%")->limit(30);

                                    return $result->pluck('full_name', 'city_id')->toArray() ?? [];
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $result = Property::select(
                                        DB::raw('CONCAT(city, " (", city_id, ") ", ", ", locale) AS full_name'))
                                        ->where('city_id', $value)->first();

                                    return $result->full_name ?? '';
                                })
                                ->native(false)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', '!in'])),
                        ],

                        'travel_date' => [
                            Grid::make()
                                ->schema([
                                    DateTimePicker::make('value_from')
                                        ->label('Travel date from')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    DateTimePicker::make('value_to')
                                        ->label('Travel date to')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'booking_date' => [
                            Grid::make()
                                ->schema([
                                    DateTimePicker::make('value_from')
                                        ->label('Booking date from')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    DateTimePicker::make('value_to')
                                        ->label('Booking date to')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'date_of_stay' => [
                            Grid::make()
                                ->schema([
                                    DateTimePicker::make('value_from')
                                        ->label('Date of stay from')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    DateTimePicker::make('value_to')
                                        ->label('Date of stay to')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'total_guests' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Total guests from')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    TextInput::make('value_to')
                                        ->label('Total guests to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'days_until_departure' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Days until departure from')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    TextInput::make('value_to')
                                        ->label('Days until departure to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'nights' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Nights from')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    TextInput::make('value_to')
                                        ->label('Nights to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'rating' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Rating from')
                                        ->numeric()
                                        ->minValue(fn (): float => 1.0)
                                        ->maxValue(fn (): float => 5.5)
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    TextInput::make('value_to')
                                        ->label('Rating to')
                                        ->numeric()
                                        ->minValue(fn (): float => 1.0)
                                        ->maxValue(fn (): float => 5.5)
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'number_of_rooms' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Number of rooms from')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    TextInput::make('value_to')
                                        ->label('Number of rooms to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        'rate_code' => [
                            TextInput::make('value_from')
                                ->label('Rate code')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', '!in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Rate code')
                                ->label('Rate codes')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', '!in'])),
                        ],

                        'room_type' => [
                            TextInput::make('value_from')
                                ->label('Room type')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', '!in'])),

                            TagsInput::make('value')
                                ->placeholder('New Room type')
                                ->separator('; ')
                                ->label('Room types')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', '!in'])),
                        ],

                        'room_code' => [
                            TextInput::make('value_from')
                                ->label('Room code')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', '!in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Room code')
                                ->label('Room codes')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', '!in'])),
                        ],

                        'room_name' => [
                            TextInput::make('value_from')
                                ->label('Room name')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', '!in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Room name')
                                ->label('Room names')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', '!in'])),
                        ],

                        'meal_plan' => [
                            TextInput::make('value_from')
                                ->label('Meal plan from')
                                ->maxLength(191)
                                ->required(),
                        ],

                        'total_price' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->suffixIcon('heroicon-o-banknotes')
                                        ->label('Total Price from')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    TextInput::make('value_to')
                                        ->suffixIcon('heroicon-o-banknotes')
                                        ->label('Total Price to')
                                        ->numeric()
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],

                        default => []
                    })
                    ->columns(1)
                    ->columnStart(3)
                    ->key('dynamicFieldValue'),
            ])
            ->required()
            ->columns(4);
    }
}
