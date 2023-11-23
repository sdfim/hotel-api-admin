<?php

namespace App\Livewire;

use App\Models\GiataProperty;
use Exception;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;

class GiataTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @param Table $table
     * @return Table
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(GiataProperty::query())
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
                    ViewAction::make()
                        ->url(fn(GiataProperty $record): string => route('giata.show', $record->code))
                        ->color('info'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.giata-table');
    }
}
