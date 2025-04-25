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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
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
                        ->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('weight')
                        ->label('Priority Weighting')
                        ->numeric()
                        ->maxLength(191),
                    CustomToggle::make('is_exclude_action')
                        ->label('Exclusion Rule')
                        ->helperText('Remove a entity from the search results')
                        ->inline(false)
                        ->reactive()
                        ->afterStateUpdated(fn (Set $set, $state) => $set('price_settings_hidden', $state)),
                    TextInput::make('rule_start_date')
                        ->label('Rule Start Date')
                        ->type('date')
                        ->required()
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if (isset($this->record)) {
                                $formattedDate = $this->record->rule_start_date
                                    ? Carbon::parse($this->record->rule_start_date)->format('Y-m-d')
                                    : '';
                                $component->state($formattedDate);
                            }
                        }),

                    TextInput::make('rule_expiration_date')
                        ->label('Rule End Date')
                        ->type('date')
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            $formattedDate = isset($this->record) && $this->record->rule_expiration_date
                                ? Carbon::parse($this->record->rule_expiration_date)->format('Y-m-d')
                                : '';
                            if ($formattedDate === '2112-02-02') {
                                $formattedDate = '';
                            }
                            $component->state($formattedDate);
                        }),
                    Placeholder::make('travel_dates_explanation')
                        ->label('')
                        ->columnSpan(5)
                        ->content(fn () => new HtmlString(<<<HTML
        <button type="button" onclick="toggleCollapse()">
            <span id="toggleIcon">▼</span> IMPORTANT: Rules dates explanation
        </button>
        <div id="collapseContent" style="display: none;">
            When doing a search, the users will need to select a start and an Travel End Date.
            This rule will only apply if the "Rule Start Date" is contained within the travel dates selected by the user.
            If the "Rule Expiration Date" is provided, it must also be contained within the travel dates selected by the user.
            If the "Rule Expiration Date" is not provided, a default date of 01-01-2100 will be applied.<br>
            <br>
            For example, consider the following scenario:<br>
            Assume that you select Jan 15, $currentYear as the "Rule Start Date" and Jan 20, $currentYear
            as the "Rule Expiration Date" (if provided)<br>
            <ul class="list-disc pl-6">
                <li class="mb-2">
                    User selects Jan 10, $currentYear as the Travel Start Date <br>
                    User selects Jan 14, $currentYear as the Travel End Date <br>
                    Will this rule apply? NO
                </li>
                <li class="mb-2">
                    User selects Jan 12, $currentYear as the Travel Start Date <br>
                    User selects Jan 18, $currentYear as the Travel End Date <br>
                    Will this rule apply? NO
                </li>
                <li class="mb-2">
                    User selects Jan 18, $currentYear as the Travel Start Date <br>
                    User selects Jan 24, $currentYear as the Travel End Date <br>
                    Will this rule apply? NO
                </li>
                <li class="mb-2">
                    User selects Jan 15, $currentYear as the Travel Start Date <br>
                    User selects Jan 18, $currentYear as the Travel End Date <br>
                    Will this rule apply? YES
                </li>
                <li class="mb-2">
                    User selects Jan 17, $currentYear as the Travel Start Date <br>
                    User selects Jan 20, $currentYear as the Travel End Date <br>
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
                        )),
                ])
                ->columns(5),

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
                        ];

                        if ($this->isSrCreator) {
                            $options['room_type_cr'] = 'Room type';
                        } else {
                            $options['room_type'] = 'Room type';
                        }

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
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->readOnly(fn (Get $get): bool => $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),

                                    DateTimePicker::make('value_to')
                                        ->label('Travel date to')
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between' && $get('compare') !== '<')
                                        ->visible(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<'),
                                ])
                                ->columns(2),
                        ],
                        'booking_date' => [
                            Grid::make()
                                ->schema([
                                    DateTimePicker::make('value_from')
                                        ->label('Booking date from')
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') !== '<')
                                        ->readOnly(fn (Get $get): bool => $get('compare') === '<')
                                        ->visible(fn (Get $get): bool => $get('compare') !== '<'),
                                    DateTimePicker::make('value_to')
                                        ->label('Booking date to')
                                        ->time(false)
                                        ->format('Y-m-d')
                                        ->displayFormat('m/d/Y')
                                        ->required(fn (Get $get): bool => $get('compare') === 'between' || $get('compare') === '<')
                                        ->readOnly(fn (Get $get): bool => $get('compare') !== 'between' && $get('compare') !== '<')
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
                                        ->required(),
                                    TextInput::make('value_to')
                                        ->label('Days until departure to')
                                        ->numeric()
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
