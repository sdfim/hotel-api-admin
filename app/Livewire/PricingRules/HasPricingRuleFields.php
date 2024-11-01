<?php

namespace App\Livewire\PricingRules;

use App\Helpers\Strings;
use App\Models\Channel;
use App\Models\Property;
use App\Models\Supplier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

trait HasPricingRuleFields
{
    public function pricingRuleFields(): array
    {
        $currentYear = date('Y');

        return [
            Fieldset::make('General settings')
                ->schema([
                    TextInput::make('name')
                        ->label('Rule name')
                        ->maxLength(191)
                        ->unique(ignoreRecord: true)
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
                        ->required(),
                    Placeholder::make('travel_dates_explanation')
                        ->label('IMPORTANT: Rules dates explanation')
                        ->columnSpan(3)
                        ->content(fn() => new HtmlString(<<<HTML
        <button type="button" onclick="toggleCollapse()">
            <span id="toggleIcon">▼</span> Toggle Explanation
        </button>
        <div id="collapseContent" style="display: none;">
            When doing a search, the users will need to select a start and an end travel date.
            This rule will only apply if the "Rule Start Date" and the "Rule Expiration Date" are contained
            within the travel dates selected by the user.<br>
            <br>
            For example, consider the following scenario:<br>
            Assume that you select Jan 15, $currentYear as the "Rule Start Date" and Jan 20, $currentYear
            as the "Rule Expiration Date"<br>
            <ul class="list-disc pl-6">
                <li class="mb-2">
                    User selects Jan 10, $currentYear as the start travel date <br>
                    User selects Jan 14, $currentYear as the end travel date <br>
                    Will this rule apply? NO
                </li>
                <li class="mb-2">
                    User selects Jan 12, $currentYear as the start travel date <br>
                    User selects Jan 18, $currentYear as the end travel date <br>
                    Will this rule apply? NO
                </li>
                <li class="mb-2">
                    User selects Jan 18, $currentYear as the start travel date <br>
                    User selects Jan 24, $currentYear as the end travel date <br>
                    Will this rule apply? NO
                </li>
                <li class="mb-2">
                    User selects Jan 15, $currentYear as the start travel date <br>
                    User selects Jan 18, $currentYear as the end travel date <br>
                    Will this rule apply? YES
                </li>
                <li class="mb-2">
                    User selects Jan 17, $currentYear as the start travel date <br>
                    User selects Jan 20, $currentYear as the end travel date <br>
                    Will this rule apply? YES
                </li>
            </ul>
        </div>
        <script>
            function toggleCollapse() {
                var content = document.getElementById('collapseContent');
                var icon = document.getElementById('toggleIcon');
                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    icon.textContent = '▲';
                } else {
                    content.style.display = 'none';
                    icon.textContent = '▼';
                }
            }
        </script>
    HTML
                        ))
                ])
                ->columns(3),
            Fieldset::make('Price settings')
                ->schema([
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
                        ->afterStateUpdated(fn(?string $state, Set $set) => $state ?: $set('price_value', null)),
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
                                    'room_code' => 'Room code',
                                    'room_name' => 'Room name',
                                    'meal_plan' => 'Meal plan / Board basis',
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
                                    'supplier_id', 'channel_id', 'property', 'destination', 'rate_code', 'room_type', 'room_code', 'room_name', 'meal_plan' => [
                                        '=' => '=',
                                        '!=' => '!=',
                                    ],
                                    default => [
                                        '=' => '=',
                                        '!=' => '!=',
                                        '<' => '<',
                                        '>' => '>',
                                        'between' => 'between',
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
                                        Select::make('value_from')
                                            ->label('Property')
                                            ->searchable()
                                            ->getSearchResultsUsing(function (string $search): ?array {
                                                $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                                                $result = Property::select(
                                                    DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                                                    ->whereRaw("MATCH(name) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                                                    ->limit(30);

                                                return $result->pluck('full_name', 'code')->toArray() ?? [];
                                            })
                                            ->getOptionLabelUsing(function (string $value): ?string {
                                                return Property::select(DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'))
                                                    ->where('code', $value)->first()?->full_name;
                                            })
                                    ],
                                    'destination' => [
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
                                            ->multiple(fn(Get $get): bool => in_array($get('compare'), ['in', 'not_in']))
                                            ->required(),
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
                                            ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                            ->disabled(fn(Get $get): bool => $get('compare') !== 'between')
                                            ->readonly(fn(Get $get): bool => $get('compare') === 'between'),
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
                                            ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                            ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                            ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                            ->visible(fn(Get $get): bool => $get('compare') === 'between'),
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
                                            ->visible(fn(Get $get): bool => $get('compare') === 'between'),
                                    ],
                                    'rate_code' => [
                                        TextInput::make('value_from')
                                            ->label('Rate code from')
                                            ->maxLength(191)
                                            ->required(),
                                    ],
                                    'room_type' => [
                                        TextInput::make('value_from')
                                            ->label('Room type from')
                                            ->maxLength(191)
                                            ->required(),
                                    ],
                                    'room_code' => [
                                        TextInput::make('value_from')
                                            ->label('Room code')
                                            ->maxLength(191)
                                            ->required(),
                                    ],
                                    'room_name' => [
                                        TextInput::make('value_from')
                                            ->label('Room name')
                                            ->maxLength(191)
                                            ->required(),
                                    ],
                                    'meal_plan' => [
                                        TextInput::make('value_from')
                                            ->label('Meal plan from')
                                            ->maxLength(191)
                                            ->required(),
                                    ],
                                    default => []
                                })
                                ->columns()
                                ->columnStart(3)
                                ->key('dynamicFieldValue')])
                        ->required()
                        ->columns(4)])
                ->columns(1),
        ];
    }
}
