<?php

namespace App\Livewire\PricingRules;

use App\Helpers\Strings;
use App\Livewire\Components\CustomRepeater;
use App\Models\Channel;
use App\Models\Property;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\HotelContentRepository\Livewire\Components\CustomToggle;
use Modules\HotelContentRepository\Models\HotelRoom;

trait HasPricingRuleFields
{
    public function pricingRuleFields(string $type): array
    {
        $currentYear = date('Y');

        return [
            Placeholder::make('is_sr_creator')
                ->label($type === 'create'
                    ? 'The rule is being created from the Supplier Repository.'
                    : 'The rule was created from the Supplier Repository.')
                ->visible($this->isSrCreator),
            Fieldset::make('General Setting')
                ->schema([
                    TextInput::make('name')
                        ->label('Rule name')
                        ->maxLength(191)
                        ->required()
                    ->columnSpan(2),
                    TextInput::make('weight')
                        ->label('Priority Weighting')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->maxLength(191),
                    CustomToggle::make('is_exclude_action')
                        ->label('Exclusion Rule')
                        ->tooltip("Makes this rule exclusive — removes\nRoom name (supplier_room_name),\nRoom type (room_type),\nRate code (rate_plan_code) \nfrom the pricingApi response")
                        ->helperText('Remove a items from the pricing response')
                        ->inline(false)
                        ->reactive()
                        ->afterStateUpdated(fn (Set $set, $state) => $set('price_settings_hidden', $state)),
                    DateTimePicker::make('rule_start_date')
                        ->label('Rule Start Date')
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('m/d/Y')
                        ->default(Carbon::now()->format('Y-m-d'))
                        ->required(),

                    DateTimePicker::make('rule_expiration_date')
                        ->label('Rule End Date')
                        ->native(false)
                        ->time(false)
                        ->format('Y-m-d')
                        ->displayFormat('m/d/Y'),
                ])
                ->columns(6),

            Fieldset::make('Price Setting')
                ->schema([
                    Select::make('manipulable_price_type')
                        ->label('Manipulable Price Type')
                        ->options([
                            'total_price' => 'Total Price',
                            'net_price' => 'Net Price',
                            'exclude_action' => '',
                        ])
                        ->required(),
                    TextInput::make('price_value')
                        ->label('Price Value')
                        ->numeric()
                        ->required()
                        ->suffixIcon(function (Get $get) {
                            return match ($get('price_value_type')) {
                                null, '' => false,
                                'fixed_value' => 'heroicon-o-banknotes',
                                'percentage' => 'heroicon-o-receipt-percent',
                                'exclude_action' => 'heroicon-o-banknotes',
                            };
                        }),
                    Select::make('price_value_type')
                        ->label('Price Value Type')
                        ->options([
                            'fixed_value' => 'Fixed Value',
                            'percentage' => 'Percentage',
                            'exclude_action' => '',
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
                            'not_applicable' => 'N/A',
                            'exclude_action' => '',
                        ])
                        ->required(),
                ])
                ->columns(4)
                ->hidden(fn (Get $get) => $get('is_exclude_action')),

            Fieldset::make('Сonditions')
                ->schema([
                    $this->getBaseRepiter(),
                ])
                ->columns(1),
        ];
    }

    private function getBaseRepiter(?string $group_type = 'conditions'): Repeater
    {
        return CustomRepeater::make($group_type)
            ->label('')
            ->addActionLabel($customButtonLabel ?? 'Add condition')
            ->schema([
                Select::make('field')
                    ->options(function () {
                        $options = [
                            'general' => [
                                'supplier_id' => 'Supplier ID',
                                'channel_id' => 'Channel ID',
                                'total_price' => 'Total price',
                            ],
                            'location' => [
                                'destination' => 'Destination',
                                'property' => 'Property',
                                'rate_code' => 'Rate code',
                                'room_name' => 'Room name',
                            ],
                            'dates' => [
                                'date_of_stay' => 'Date of stay',
                                'travel_date' => 'Travel date',
                                'booking_date' => 'Booking date',
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

                        if ($this->isSrCreator) {
                            $options['location']['room_type_cr'] = 'Room type';
                        } else {
                            $options['location']['room_type'] = 'Room type';
                        }

                        return $options;
                    })
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Select $component, $state, Get $get) {
                        // Validation logic
                        $selectedFields = collect($get('../../conditions'))
                            ->pluck('field')
                            ->filter()
                            ->unique()
                            ->values(); // Re-index the collection
                        $selectedFields->pop(); // Remove the last element
                        $selectedFields = $selectedFields->toArray();

                        if (in_array($state, $selectedFields, true)) {
                            Notification::make()
                                ->title('Validation Error')
                                ->body('This field is already selected. Please choose another one.')
                                ->danger()
                                ->send();

                            $component->state(null); // Reset the state if validation fails

                            return;
                        }

                        // Additional validation
                        if (($state === 'rate_code' || $state === 'room_name' || $state === 'room_type' || $state === 'room_type_cr')
                            && ! in_array('property', $selectedFields, true)) {
                            Notification::make()
                                ->title('Validation Error')
                                ->body('The "rate_code" field requires a selected "property".')
                                ->danger()
                                ->send();

                            $component->state(null); // Reset the state if validation fails

                            return;
                        }

                        // Existing functionality
                        $component
                            ->getContainer()
                            ->getComponent('dynamicFieldValue')
                            ->getChildComponentContainer()
                            ->fill();
                    }),
                Select::make('compare')
                    ->options(fn (Get $get): array => match ($get('field')) {
                        'supplier_id', 'channel_id', 'meal_plan' => [
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                        ],
                        'property', 'destination', 'room_type', 'room_type_cr', 'room_code', 'room_name', 'rate_code' => [
                            'in' => 'In List',
                            '!in' => 'Not In List',
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                        ],
                        'days_until_departure' => [
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                            '<' => '< (Less Than)',
                            '>' => '> (Greater Than)',
                            '<=' => '<= (Less Than or Equal To)',
                            '>=' => '>= (Greater Than or Equal To)',
                            'between' => 'Between',
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

                        'property' => [
                            Select::make('value')
                                ->label('Property')
                                ->searchable()
                                ->multiple()
                                ->getSearchResultsUsing(function (string $search): ?array {
                                    $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                                    $result = Property::select(
                                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name, code'))
                                        ->whereRaw("MATCH(search_index) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                                        ->limit(100);

                                    return $result->pluck('full_name', 'code')
                                        ->mapWithKeys(function ($full_name, $code) {
                                            return [$code => $full_name.' ('.$code.')'];
                                        })
                                        ->toArray() ?? [];
                                })
                                ->getOptionLabelsUsing(function (array $values): ?array {
                                    $properties = Property::select(DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                                        ->whereIn('code', $values)
                                        ->get()
                                        ->mapWithKeys(function ($property) {
                                            return [$property->code => $property->full_name.' ('.$property->code.')'];
                                        })
                                        ->toArray();

                                    return $properties;
                                })
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),

                            Select::make('value_from')
                                ->label('Property')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search): ?array {
                                    $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                                    $result = Property::select(
                                        DB::raw('CONCAT(name, " (", city, ", ", locale, ", ", code, ")") AS full_name, code'))
                                        ->whereRaw("MATCH(search_index) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                                        ->limit(100);

                                    return $result->pluck('full_name', 'code')->toArray() ?? [];
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $property = Property::select(DB::raw('CONCAT(name, " (", city, ", ", locale, ", ", code, ")") AS full_name'))
                                        ->where('code', $value)
                                        ->first();

                                    return $property ? $property->full_name : null;
                                })
                                ->required()
                                ->disabled(fn (Get $get) => $this->isSrCreator)
                                ->dehydrated()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in'])),
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
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),

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
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in'])),
                        ],

                        'room_type_cr' => [
                            Select::make('value')
                                ->label('Room type (SR)')
                                ->searchable()
                                ->multiple()
                                ->getSearchResultsUsing(function (string $search): array {
                                    return HotelRoom::query()
                                        ->where('external_code', 'like', "%$search%")
                                        ->orWhere('name', 'like', "%$search%")
                                        ->limit(30)
                                        ->get()
                                        ->mapWithKeys(function ($room) {
                                            return [$room->id => "{$room->external_code} ({$room->name})"];
                                        })
                                        ->toArray();
                                })
                                ->getOptionLabelsUsing(function (array $values): array {
                                    return HotelRoom::whereIn('id', $values)
                                        ->get()
                                        ->mapWithKeys(function ($room) {
                                            return [$room->id => "{$room->external_code} ({$room->name})"];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),

                            Select::make('value_from')
                                ->label('Room type (SR)')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search): array {
                                    return HotelRoom::query()
                                        ->where('external_code', 'like', "%$search%")
                                        ->orWhere('name', 'like', "%$search%")
                                        ->limit(30)
                                        ->get()
                                        ->mapWithKeys(function ($room) {
                                            return [$room->id => "{$room->external_code} ({$room->name})"];
                                        })
                                        ->toArray();
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $room = HotelRoom::find($value);

                                    return $room ? "{$room->external_code} ({$room->name})" : null;
                                })
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in'])),
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

                        'total_guests' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Total guests from')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Total guests to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],

                        'days_until_departure' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Days until departure from')
                                        ->numeric()
                                        ->maxValue(31)
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Days until departure to')
                                        ->numeric()
                                        ->maxValue(31)
                                        ->required(fn (Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],

                        'nights' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Nights from')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Nights to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between'),
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
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Rating to')
                                        ->numeric()
                                        ->minValue(fn (): float => 1.0)
                                        ->maxValue(fn (): float => 5.5)
                                        ->required(fn (Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],

                        'number_of_rooms' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Number of rooms from')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Number of rooms to')
                                        ->numeric()
                                        ->required(fn (Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],

                        'rate_code' => [
                            TextInput::make('value_from')
                                ->label('Rate code')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in']))
                                ->required(fn (Get $get) => $get('is_exclude_action')),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Rate code')
                                ->label('Rate codes')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],

                        'room_type' => [
                            TextInput::make('value_from')
                                ->label('Room type')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->placeholder('New Room type')
                                ->separator('; ')
                                ->label('Room types')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],

                        'room_code' => [
                            TextInput::make('value_from')
                                ->label('Room code')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Room code')
                                ->label('Room codes')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],

                        'room_name' => [
                            TextInput::make('value_from')
                                ->label('Room name')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn (Get $get) => ! in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Room name')
                                ->label('Room names')
                                ->required()
                                ->visible(fn (Get $get) => in_array($get('compare'), ['in', 'not_in'])),
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
