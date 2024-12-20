<?php

namespace Modules\HotelContentRepository\Livewire\ProductDepositInformation;

use App\Helpers\Strings;
use App\Livewire\Components\CustomRepeater;
use App\Models\Channel;
use App\Models\Property;
use App\Models\Supplier;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Modules\HotelContentRepository\Models\HotelRoom;

trait DepositFieldTrait
{
    public function schemeForm($record = null): array
    {
        return [
            Hidden::make('product_id')->default($this->productId),

            Fieldset::make('General settings')
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->columnSpan(2)
                        ->maxLength(191)
                        ->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('start_date')
                        ->label('Start Date')
                        ->type('date')
                        ->required()
                        ->afterStateHydrated(function (TextInput $component) use ($record) {
                            if (isset($record)) {
                                $formattedDate = $record->start_date
                                    ? Carbon::parse($record->start_date)->format('Y-m-d')
                                    : '';
                                $component->state($formattedDate);
                            }
                        }),

                    TextInput::make('expiration_date')
                        ->label('Expiration Date')
                        ->type('date')
                        ->afterStateHydrated(function (TextInput $component) use ($record) {
                            $formattedDate = isset($record) && $record->expiration_date
                                ? Carbon::parse($record->expiration_date)->format('Y-m-d')
                                : '';
                            if ($formattedDate === '2112-02-02') {
                                $formattedDate = '';
                            }
                            $component->state($formattedDate);
                        }),
//                ])
//                ->columns(4),
//            Fieldset::make('Settings')
//                ->schema([
                    Select::make('manipulable_price_type')
                        ->label('Manipulable price type')
                        ->options([
                            'total_price' => 'Total Price',
                            'net_price' => 'Net Price',
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
                        ->afterStateUpdated(fn(?string $state, Set $set) => $state ?: $set('price_value', null))
                    ,
                    Select::make('price_value_target')
                        ->label('Price value target')
                        ->options([
                            'per_guest' => 'Per Guest',
                            'per_room' => 'Per Room',
                            'per_night' => 'Per Night',
                            'not_applicable' => 'N/A',
                        ])
                        ->required(),
                ])
                ->columns(4),
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
                        return [
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
                            'meal_plan' => 'Meal plan / Board basis',
                            'rate_code' => 'Rate code',
//                                'room_code' => 'Room code',
                            'room_name' => 'Room name',
                            'room_type' => 'Room type',
                        ];
                    })
//                    ->live()
                    ->required()
                    ->afterStateUpdated(fn(Select $component) => $component
                        ->getContainer()
                        ->getComponent('dynamicFieldValue')
                        ->getChildComponentContainer()
                        ->fill()
                    ),
                Select::make('compare')
                    ->options(fn(Get $get): array => match ($get('field')) {
                        'supplier_id', 'channel_id', 'meal_plan'  => [
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                        ],
                        'property', 'destination', 'room_type', 'room_type_cr', 'room_code', 'room_name', 'rate_code' => [
                            'in' => 'In List',
                            '!in' => 'Not In List',
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                        ],
                        default => [
                            '=' => 'Equals',
                            '!=' => 'Not Equals',
                            '<' => '<',
                            '>' => '>',
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
                    ->schema(components: fn(Get $get): array => match ($get('field')) {
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
                                            return [$code => $full_name . ' (' . $code . ')'];
                                        })
                                        ->toArray() ?? [];
                                })
                                ->getOptionLabelsUsing(function (array $values): ?array {
                                    $properties = Property::select(DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                                        ->whereIn('code', $values)
                                        ->get()
                                        ->mapWithKeys(function ($property) {
                                            return [$property->code => $property->full_name . ' (' . $property->code . ')'];
                                        })
                                        ->toArray();
                                    return $properties;
                                })
                                ->required()
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),

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
                                ->dehydrated()
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),
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
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),

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
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),
                        ],

                        'room_type_cr' => [
                            Select::make('value')
                                ->label('Room type (SR)')
                                ->searchable()
                                ->multiple()
                                ->getSearchResultsUsing(function (string $search): array {
                                    return HotelRoom::query()
                                        ->where('hbsi_data_mapped_name', 'like', "%$search%")
                                        ->orWhere('name', 'like', "%$search%")
                                        ->limit(30)
                                        ->get()
                                        ->mapWithKeys(function ($room) {
                                            return [$room->id => "{$room->hbsi_data_mapped_name} ({$room->name})"];
                                        })
                                        ->toArray();
                                })
                                ->getOptionLabelsUsing(function (array $values): array {
                                    return HotelRoom::whereIn('id', $values)
                                        ->get()
                                        ->mapWithKeys(function ($room) {
                                            return [$room->id => "{$room->hbsi_data_mapped_name} ({$room->name})"];
                                        })
                                        ->toArray();
                                })
                                ->required()
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),

                            Select::make('value_from')
                                ->label('Room type (SR)')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search): array {
                                    return HotelRoom::query()
                                        ->where('hbsi_data_mapped_name', 'like', "%$search%")
                                        ->orWhere('name', 'like', "%$search%")
                                        ->limit(30)
                                        ->get()
                                        ->mapWithKeys(function ($room) {
                                            return [$room->id => "{$room->hbsi_data_mapped_name} ({$room->name})"];
                                        })
                                        ->toArray();
                                })
                                ->getOptionLabelUsing(function ($value): ?string {
                                    $room = HotelRoom::find($value);
                                    return $room ? "{$room->hbsi_data_mapped_name} ({$room->name})" : null;
                                })
                                ->required()
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),
                        ],

                        'travel_date' => [
                            Grid::make()
                                ->schema([
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
                                        ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                        ->displayFormat('d-m-Y')
                                        ->required(),
                                    DateTimePicker::make('value_to')
                                        ->label('Booking date to')
                                        ->native(false)
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('d-m-Y')
                                        ->required(fn(Get $get): bool => $get('compare') === 'between')
                                        ->disabled(fn(Get $get): bool => $get('compare') !== 'between')
                                        ->readonly(fn(Get $get): bool => $get('compare') === 'between'),
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
                                        ->required(fn(Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn(Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],
                        'days_until_departure' => [
                            Grid::make()
                                ->schema([
                                    TextInput::make('value_from')
                                        ->label('Days until departure from')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Days until departure to')
                                        ->numeric()
                                        ->required(fn(Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                        ->required(fn(Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn(Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],
                        'rating' => [
                            Grid::make()
                                ->schema([
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
                                        ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                        ->required(fn(Get $get): bool => $get('compare') === 'between')
                                        ->readOnly(fn(Get $get): bool => $get('compare') !== 'between')
                                        ->visible(fn(Get $get): bool => $get('compare') === 'between'),
                                ])
                                ->columns(2),
                        ],
                        'rate_code' => [
                            TextInput::make('value_from')
                                ->label('Rate code')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Rate code')
                                ->label('Rate codes')
                                ->required()
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],
                        'room_type' => [
                            TextInput::make('value_from')
                                ->label('Room type')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->placeholder('New Room type')
                                ->separator('; ')
                                ->label('Room types')
                                ->required()
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],
                        'room_code' => [
                            TextInput::make('value_from')
                                ->label('Room code')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Room code')
                                ->label('Room codes')
                                ->required()
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],
                        'room_name' => [
                            TextInput::make('value_from')
                                ->label('Room name')
                                ->maxLength(191)
                                ->required()
                                ->visible(fn(Get $get) => !in_array($get('compare'), ['in', 'not_in'])),

                            TagsInput::make('value')
                                ->separator('; ')
                                ->placeholder('New Room name')
                                ->label('Room names')
                                ->required()
                                ->visible(fn(Get $get) => in_array($get('compare'), ['in', 'not_in'])),
                        ],
                        'meal_plan' => [
                            TextInput::make('value_from')
                                ->label('Meal plan from')
                                ->maxLength(191)
                                ->required(),
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
