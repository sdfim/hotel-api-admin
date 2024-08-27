<?php

namespace App\Livewire;

use App\Models\GiataGeography;
use App\Models\Property;
use Exception;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
use Illuminate\View\View;
use Livewire\Component;

class GiataTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    private static function getCityById($city_id) {
      return GiataGeography::query()->where('city_id', '=', (int) $city_id)->first();
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
                          ->getOptionLabelUsing(fn ($value) => GiataTable::getCityById($value)->city_name)
                          ->reactive()
                          ->afterStateUpdated(function ($set, $state) {
                              $city = GiataTable::getCityById($state);

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
                          ->disabled(!$isEditable),
                ]),
        ];
    }

    private static function preparePropertyData (array $data) {
        $data['property_auto_updates'] = 0;
        $data['city_id'] = (int) $data['city_id'];
        // TODO: Should we remove city_name from properties table? Or is the refactor too big?
        $data['city'] = GiataTable::getCityById($data['city_id'])->city_name;
        return $data;
    }

    private static function createProperty (array $data) {
        $data = GiataTable::preparePropertyData($data);
        // $data['source'] = PropertiesSourceEnum::Custom->value;
        $city = GiataTable::getCityById($data['city_id']);
        $empty = json_decode ("{}");
        $data['cross_references'] = json_encode($empty);
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
        $data['url'] = json_encode([$data['url']]);
        return $data;
    }

    private static function updateProperty (Property $property, array $data) {
        $data = GiataTable::preparePropertyData($data);
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
                ->form(fn() => GiataTable::getFormSchema(true))
                ->mutateFormDataUsing(fn (array $data) => GiataTable::createProperty($data))
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
                    ->view('dashboard.giata.column.name-field'),
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
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->color('info')
                        ->modalHeading('Property Details - Edit')
                        ->form(fn() => GiataTable::getFormSchema(true))
                        ->action(fn($record, $data) => GiataTable::updateProperty($record, $data)),
                    ViewAction::make()
                        ->color('info')
                        ->form(fn() => GiataTable::getFormSchema(false))
                        ->modalHeading('Property Details - View'),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.giata-table');
    }
}
