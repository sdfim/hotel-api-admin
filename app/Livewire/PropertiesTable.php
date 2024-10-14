<?php

namespace App\Livewire;

use App\Models\GiataGeography;
use App\Models\Mapping;
use App\Models\Property;
use Exception;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
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
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
// use Modules\API\Suppliers\Enums\PropertiesSourceEnum; // TODO: This is not being found, unclear why.
use Modules\Enums\SupplierNameEnum;
use stdClass;

class PropertiesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    private static function getCityById($city_id) {
      return GiataGeography::query()->where('city_id', '=', (int) $city_id)->first();
    }

    private static function getMapSchema(): array {
        return [
            Grid::make(1)
                ->schema([
                    Repeater::make('mappings')
                        ->label('Mappings')
                        ->columns(2)
                        ->relationship('mappings')
                        ->schema([
                            Select::make('supplier')
                                ->label('Supplier')
                                ->options(fn ()=> array_combine(SupplierNameEnum::getValues(), SupplierNameEnum::getValues()))
                                ->default(fn ($record) => $record?->supplier)
                                ->required()
                                ->distinct(),

                            TextInput::make('supplier_id')
                                ->label('Supplier ID')
                                ->default(fn ($record) => $record?->supplier_id)
                                ->required(),

                            Hidden::make('match_percentage')
                                ->default(fn ($record) => $record?->match_percentage ?? 100),
                        ])
                        ->default(fn($record) => $record->mappings->toArray())
                        ->addActionLabel('Add a Mapping'),
              ])
        ];
    }

    private static function getFormSchema(bool $isEditable = true): array
    {
        return [
            Grid::make(2)
                ->schema([

                      TextInput::make('code')
                          ->label('Code')
                          ->disabled(!$isEditable)
                          ->required(),

                      TextInput::make('name')
                          ->label('Name')
                          ->disabled(!$isEditable)
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
                          ->disabled(!$isEditable)
                          ->required(),

                      Hidden::make('locale_id')
                          ->required(),

                      TextInput::make('locale')
                          ->label('Locale')
                          ->readOnly()
                          ->required(),

                      TextInput::make('mapper_address')
                          ->label('Address')
                          ->disabled(!$isEditable)
                          ->required(),

                      TextInput::make('mapper_postal_code')
                          ->label('Postal Code')
                          ->numeric()
                          ->disabled(!$isEditable),

                      TextInput::make('rating')
                          ->label('Rating')
                          ->numeric()
                          ->disabled(!$isEditable),

                      TextInput::make('latitude')
                          ->label('Latitude')
                          ->numeric()
                          ->minValue(-90)
                          ->maxValue(90)
                          ->disabled(!$isEditable),

                      TextInput::make('longitude')
                          ->label('Longitude')
                          ->numeric()
                          ->minValue(-90)
                          ->maxValue(90)
                          ->disabled(!$isEditable),

                      TextInput::make('mapper_phone_number')
                          ->label('Phone')
                          ->disabled(!$isEditable),

                      TextInput::make('url')
                          ->label('URL')
                          ->default(fn ($record) => $record->url[0] ?? '')
                          ->afterStateHydrated(function (TextInput $component, $state) {
                            $component->state($state[0] ?? '');
                          })
                          ->afterStateUpdated(function ($state, $set) {
                            $set('url', [$state]);
                          })
                          ->disabled(!$isEditable),
                ]),
        ];
    }

    private static function preparePropertyData (array $data) {
        $data['property_auto_updates'] = 0;
        $data['city_id'] = (int) $data['city_id'];
        $data['city'] = PropertiesTable::getCityById($data['city_id'])->city_name;
        return $data;
    }

    private static function createProperty (array $data) {
        $data = PropertiesTable::preparePropertyData($data);
        // $data['source'] = PropertiesSourceEnum::Custom->value;
        $city = PropertiesTable::getCityById($data['city_id']);
        $data['cross_references'] = new stdClass(); // Empty Object
        $data['address'] = [
            "UseType"       => "7",
            "CityName"      => $city->city_name,
            "PostalCode"    => $data['mapper_postal_code'],
            // "StreetNmbr": "1", // TODO
            // "AddressLine": "Vicinale Santa Chiara", // TODO
            "CountryName"   => $city->country_code,
            "FormattedInd"  => "true"
        ];
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['last_updated'] = date('Y-m-d H:i:s');
        $data['phone'] = [
          "PhoneNumber"   => $data['mapper_phone_number'],
          "PhoneTechType" => "1"
        ];
        $data['position'] = [
          'Latitude' => $data['latitude'],
          'Longitude' => $data['longitude'],
          'PositionAccuracy' => 1
        ];
        $data['source'] = 'Custom';
        // $data['source'] = PropertiesSourceEnum::Custom->value; // TODO: This is failing, see import.
        return $data;
    }

    private static function updateProperty (Property $property, array $data) {
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
                ->form(fn() => PropertiesTable::getFormSchema(true))
                ->mutateFormDataUsing(fn (array $data) => PropertiesTable::createProperty($data))
            ])
            ->query(Property::query())
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('code', $search);
                    }, isIndividual: true),
                ViewColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(isIndividual: true)
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
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('mapper_phone_number')
                    ->label('Phone')
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('source')
                    ->label('Type')
                    ->toggleable()
                    ->searchable(isIndividual: true),
            ])
            ->actions([
              ActionGroup::make([
                    Action::make('map')
                        ->label('Mappings')
                        ->icon('heroicon-m-link')
                        ->modalHeading(fn (Property $record) => 'Property Mapping - ' . $record->code . ' ' . $record->name)
                        ->form(fn() => PropertiesTable::getMapSchema()),
                    EditAction::make('edit')
                        ->modalHeading('Property Details - Edit')
                        ->form(fn() => PropertiesTable::getFormSchema(true))
                        ->action(fn($record, $data) => PropertiesTable::updateProperty($record, $data)),
                    ViewAction::make('view')
                        ->form(fn() => PropertiesTable::getFormSchema(false))
                        ->modalHeading('Property Details - View'),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.properties-table');
    }
}
