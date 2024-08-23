<?php

namespace App\Livewire;

use App\Models\Property;
use Exception;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
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

    private static function getFormSchema(bool $isEditable = true): array
    {
        return [
            Grid::make(2)
                ->schema([

                      TextInput::make('code')
                          ->label('Code')
                          ->disabled(!$isEditable),
                          
                      TextInput::make('name')
                          ->label('Name')
                          ->disabled(!$isEditable),

                      TextInput::make('city')
                          ->label('City')
                          ->disabled(!$isEditable),
                          
                      TextInput::make('rating')
                          ->label('Rating')
                          ->disabled(!$isEditable),

                      TextInput::make('city_id')
                          ->label('City id')
                          ->disabled(!$isEditable),
                      
                      TextInput::make('locale')
                          ->label('Locale')
                          ->disabled(!$isEditable),

                      TextInput::make('latitude')
                          ->label('Latitude')
                          ->disabled(!$isEditable),

                      TextInput::make('longitude')
                          ->label('Longitude')
                          ->disabled(!$isEditable),

                      TextInput::make('mapper_address')
                          ->label('Address')
                          ->disabled(!$isEditable),

                      TextInput::make('mapper_phone_number')
                          ->label('Phone')
                          ->disabled(!$isEditable),

                ]),
        ];
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
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
                TextColumn::make('city_id')
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
                        ->action(fn ($record, array $data) => $record->update($data)),
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
