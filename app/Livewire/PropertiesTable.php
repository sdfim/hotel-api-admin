<?php

namespace App\Livewire;

use App\Helpers\Strings;
use App\Livewire\Components\CustomRepeater;
use App\Models\GiataGeography;
use App\Models\Property;
use Exception;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Livewire\Components\CustomToggle;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use stdClass;

class PropertiesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    private static function getCityById($city_id)
    {
        return GiataGeography::query()->where('city_id', '=', (int) $city_id)->first();
    }

    public function query(): Builder
    {
        $query = Property::query();

        if (request()->has('giata_id')) {
            $giataId = request('giata_id');
            $query->whereHas('mappings', function ($q) use ($giataId) {
                $q->where('giata_id', $giataId);
            });
        }

        return $query;
    }

    private static function getMapSchema(): array
    {
        return [
            Grid::make(1)
                ->schema([
                    CustomRepeater::make('mappings')
                        ->label('Mappings')
//                        ->relationship('mappings')
                        ->schema([

                            Select::make('supplier')
                                ->hiddenLabel()
                                ->label('Supplier')
                                ->placeholder('Select a supplier')
                                ->options(function (callable $get): array {
                                    $allOptions = SupplierNameEnum::options();
                                    $currentSupplier = $get('supplier');
                                    $currentMappings = $get('../../mappings') ?? [];
                                    $usedSuppliers = collect($currentMappings)
                                        ->pluck('supplier')
                                        ->filter()
                                        ->unique()
                                        ->toArray();

                                    return array_filter(
                                        $allOptions,
                                        fn ($key) => ! in_array($key, $usedSuppliers) || $key === $currentSupplier,
                                        ARRAY_FILTER_USE_KEY
                                    );
                                })
                                ->default(fn ($record) => $record?->supplier)
                                ->required()
                                ->disabled(fn ($get) => $get('is_locked'))
                                ->dehydrated(true), // <-- ADD THIS LINE

                            TextInput::make('supplier_id')
                                ->hiddenLabel()
                                ->label('Supplier ID')
                                ->placeholder('Enter supplier ID')
                                ->default(fn ($record) => $record?->supplier_id)
                                ->required()
                                ->disabled(fn ($get) => $get('is_locked'))
                                ->dehydrated(true), // <-- ADD THIS LINE

                            Select::make('match_percentage')
                                ->label('')
                                ->options([
                                    100 => 'Provided by Giata',
                                    99 => 'Finalized by Admin',
                                ])
                                ->placeholder(null)
                                ->default(fn ($record) => $record?->match_percentage ?? 98)
                                ->disabled(fn ($get) => $get('is_locked'))
                                ->dehydrated(true) // <-- ADD THIS LINE
                                ->required()
                                ->helperText(function ($get) {
                                    $matchPercentage = $get('match_percentage');

                                    return $matchPercentage < 90
                                        ? 'Handled by automated tools'
                                        : null;
                                }),

                            CustomToggle::make('is_locked')
                                ->label('edit lock')
                                ->tooltip('It is not recommended to edit the mappers provided by Giata.')
                                ->dehydrated(false)
                                ->reactive()
                                ->default(fn ($get) => ($get('match_percentage') == 100 || $get('match_percentage') < 90)),
                        ])
                        ->columns(4)
                        ->default(fn ($record) => $record->mappings->toArray())
                        ->addActionLabel('Add a Mapping'),
                ]),
        ];
    }

    private static function getFormSchema(bool $isEditable = true): array
    {
        return [
            Grid::make(2)
                ->schema([

                    TextInput::make('code')
                        ->label('Code')
                        ->disabled(! $isEditable)
                        ->required(),

                    TextInput::make('name')
                        ->label('Name')
                        ->disabled(! $isEditable)
                        ->required(),

                    Select::make('city_id')
                        ->label('City')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => GiataGeography::query()
                            ->where('city_name', 'like', "%{$search}%")
                            ->orderBy('city_name')
                            ->pluck('city_name', 'city_id')
                            ->toArray())
                        ->getOptionLabelUsing(fn ($value) => PropertiesTable::getCityById($value)->city_name)
                        ->reactive()
                        ->afterStateUpdated(function ($set, $state) {
                            $city = PropertiesTable::getCityById($state);

                            $set('locale_id', $city->locale_id);
                            $set('locale', $city->locale_name);
                        })
                        ->disabled(! $isEditable)
                        ->required(),

                    Hidden::make('locale_id')
                        ->required(),

                    TextInput::make('locale')
                        ->label('Locale')
                        ->readOnly()
                        ->required(),

                    TextInput::make('mapper_address')
                        ->label('Address')
                        ->disabled(! $isEditable)
                        ->required(),

                    TextInput::make('mapper_postal_code')
                        ->label('Postal Code')
                        ->numeric()
                        ->disabled(! $isEditable),

                    TextInput::make('rating')
                        ->label('Rating')
                        ->numeric()
                        ->disabled(! $isEditable),

                    TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->minValue(-90)
                        ->maxValue(90)
                        ->disabled(! $isEditable),

                    TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->minValue(-180)
                        ->maxValue(180)
                        ->disabled(! $isEditable),

                    TextInput::make('mapper_phone_number')
                        ->label('Phone')
                        ->disabled(! $isEditable),

                    TextInput::make('url')
                        ->label('URL')
                        ->default(fn ($record) => $record->url[0] ?? '')
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            $component->state($state[0] ?? '');
                        })
                        ->afterStateUpdated(function ($state, $set) {
                            $set('url', [$state]);
                        })
                        ->disabled(! $isEditable),
                ]),
        ];
    }

    private static function preparePropertyData(array $data)
    {
        $data['property_auto_updates'] = 0;
        $data['city_id'] = (int) $data['city_id'];
        $data['city'] = PropertiesTable::getCityById($data['city_id'])->city_name;

        return $data;
    }

    private static function createProperty(array $data)
    {
        $data = PropertiesTable::preparePropertyData($data);
        // $data['source'] = PropertiesSourceEnum::Custom->value;
        $city = PropertiesTable::getCityById($data['city_id']);
        $data['cross_references'] = new stdClass; // Empty Object
        $data['address'] = [
            'UseType' => '7',
            'CityName' => $city->city_name,
            'PostalCode' => $data['mapper_postal_code'],
            // "StreetNmbr": "1", // TODO
            // "AddressLine": "Vicinale Santa Chiara", // TODO
            'CountryName' => $city->country_code,
            'FormattedInd' => 'true',
        ];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['last_updated'] = date('Y-m-d H:i:s');
        $data['phone'] = [
            'PhoneNumber' => $data['mapper_phone_number'],
            'PhoneTechType' => '1',
        ];
        $data['position'] = [
            'Latitude' => $data['latitude'],
            'Longitude' => $data['longitude'],
            'PositionAccuracy' => 1,
        ];
        $data['source'] = 'Custom';

        // $data['source'] = PropertiesSourceEnum::Custom->value; // TODO: This is failing, see import.
        return $data;
    }

    private static function updateProperty(Property $property, array $data)
    {
        $data = PropertiesTable::preparePropertyData($data);
        $property->update($data);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->headerActions([
                CreateAction::make()
                    ->label('Create')
                    ->modalHeading('Create new property')
                    ->form(fn () => PropertiesTable::getFormSchema())
                    ->mutateFormDataUsing(fn (array $data) => PropertiesTable::createProperty($data))
                    ->visible(fn () => Gate::allows('create', Property::class)),
                ExportAction::make()->exports([
                    ExcelExport::make('table')
                        ->fromTable()
                        ->withFilename('properties_export_'.now()->format('Y_m_d_H_i_s').'.xlsx'),
                ]),
            ])
            ->query($this->query())
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('code', $search);
                    }, isIndividual: true),
                TextColumn::make('city_id')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                ViewColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        query: function (Builder $query, string $search): Builder {
                            $preparedSearchText = Strings::prepareSearchForBooleanMode($search);

                            return $query->whereRaw("MATCH(name) AGAINST('$preparedSearchText' IN BOOLEAN MODE)");
                        }
                    )
                    ->view('dashboard.properties.column.name-field'),
                TextColumn::make('city')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('rating')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('locale')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('latitude')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('longitude')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('mapper_address')
                    ->label('Address')
                    ->sortable()
                    ->wrap()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('mapper_phone_number')
                    ->label('Phone')
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('source')
                    ->label('Type')
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('provider')
                    ->label('Mapped Providers')
                    ->multiple()
                    ->options(array_combine(SupplierNameEnum::getValues(), SupplierNameEnum::getValues()))
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['values'])) {
                            $query->whereHas('mappings', function ($q) use ($data) {
                                $q->whereIn('supplier', $data['values']);
                            });
                        }
                    }),
                \Filament\Tables\Filters\SelectFilter::make('all_providers')
                    ->label('Mapped to All Providers')
                    ->multiple()
                    ->options(array_combine(SupplierNameEnum::getValues(), SupplierNameEnum::getValues()))
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['values'])) {
                            $query->whereHas('mappings', function ($q) use ($data) {
                                $q->select('giata_id')
                                    ->whereIn('supplier', $data['values'])
                                    ->groupBy('giata_id')
                                    ->havingRaw('COUNT(DISTINCT supplier) = ?', [count($data['values'])]);
                            });
                        }
                    }),
                \Filament\Tables\Filters\Filter::make('supplier_and_code')
                    ->label('Supplier & Code')
                    ->form([
                        \Filament\Forms\Components\Select::make('supplier')
                            ->label('Supplier')
                            ->options(array_combine(SupplierNameEnum::getValues(), SupplierNameEnum::getValues()))
                            ->searchable(),
                        \Filament\Forms\Components\TagsInput::make('supplier_ids')
                            ->label('Supplier Codes'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['supplier']) && ! empty($data['supplier_ids'])) {
                            $query->whereHas('mappings', function ($q) use ($data) {
                                $q->where('supplier', $data['supplier'])
                                    ->whereIn('supplier_id', $data['supplier_ids']);
                            });
                        }
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('map')
                        ->label('Mappings')
                        ->icon('heroicon-m-link')
                        ->modalHeading(fn (Property $record) => 'Property Mapping - '.$record->code.' '.$record->name)
                        ->form(fn () => PropertiesTable::getMapSchema())
                        ->fillForm(function (Property $record): array {
                            $possibleSuppliers = explode(',', config('booking-suppliers.connected_suppliers', ''));

                            return [
                                'mappings' => $record->mappings
                                    ->whereIn('supplier', $possibleSuppliers)
                                    ->map(fn ($m) => [
                                        'id' => $m->id,
                                        'supplier' => $m->supplier,
                                        'supplier_id' => $m->supplier_id,
                                        'match_percentage' => $m->match_percentage,
                                        'is_locked' => $m->is_locked,
                                    ])
                                    ->values()
                                    ->toArray(),
                            ];
                        })
                        ->action(function (Property $record, array $data) {
                            $possibleSuppliers = explode(',', config('booking-suppliers.connected_suppliers', ''));
                            $submittedMappings = collect($data['mappings'] ?? []);

                            // id, from form
                            $submittedIds = $submittedMappings
                                ->pluck('id')
                                ->filter()
                                ->values()
                                ->all();

                            // ❌ Delete removed
                            $record->mappings()
                                ->whereIn('supplier', $possibleSuppliers)
                                ->whereNotIn('id', $submittedIds)
                                ->delete();

                            // ✅ update / create
                            foreach ($submittedMappings as $mapping) {
                                $record->mappings()->updateOrCreate(
                                    ['id' => $mapping['id'] ?? null],
                                    [
                                        'supplier' => $mapping['supplier'],
                                        'supplier_id' => $mapping['supplier_id'],
                                        'match_percentage' => $mapping['match_percentage'],
                                    ]
                                );
                            }
                        })
                        ->visible(fn (Property $record) => Gate::allows('update', $record)),
                    EditAction::make('edit')
                        ->modalHeading('Property Details - Edit')
                        ->form(fn () => PropertiesTable::getFormSchema(true))
                        ->action(fn ($record, $data) => PropertiesTable::updateProperty($record, $data))
                        ->visible(fn (Property $record) => Gate::allows('update', $record)),
                    ViewAction::make('view')
                        ->form(fn () => PropertiesTable::getFormSchema(false))
                        ->modalHeading('Property Details - View'),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.properties-table');
    }
}
