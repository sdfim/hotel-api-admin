<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Table;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ExpediaTable extends Component implements HasForms, HasTable
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
            ->paginated([5, 10])
            ->query(ExpediaContent::query()->orderBy('rating', 'desc'))
            ->columns([
                TextColumn::make('property_id')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
				ViewColumn::make('name')
					->toggleable()
					->sortable()
					->searchable(isIndividual: true)
					->view('dashboard.expedia.column.name-field'),
				TextColumn::make('rating')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->numeric()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
				TextColumn::make('latitude')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
				TextColumn::make('longitude')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
				TextColumn::make('phone')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                ViewColumn::make('address')->view('dashboard.expedia.column.address-field')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                ViewColumn::make('mapperGiataExpedia.giata_id')->label('Giata id')->view('dashboard.expedia.column.giata_id')
                    ->searchable(isIndividual: true),
                TextColumn::make('mapperGiataExpedia.property_id')
                    ->searchable(isIndividual: true)
                    ->default('')
                    ->label('Type')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if (count($record->mapperGiataExpedia) > 1) {
                            return 'Multivariate';
                        } else if (count($record->mapperGiataExpedia) == 1) {
                            return 'Single';
                        }
                        return 'Empty';
                    })
                    ->toggleable(),
                TextColumn::make('mapperGiataExpedia.step')
                    ->searchable(isIndividual: true)
                    ->label('Version')
                    ->formatStateUsing(function ($record) {
                        if (count($record->mapperGiataExpedia) > 1) {
                            return 'Autom';
                        }
                        if (count($record->mapperGiataExpedia) == 1) {
                            if ($record->mapperGiataExpedia[0]->step == 100) {
                                return 'Manual';
                            } else {
                                return 'Auto';
                            }
                        }
                        return 'Empty';
                    })
                    ->toggleable(),
                ViewColumn::make('edit')->view('dashboard.expedia.column.add-giata')->toggleable(),
            ])
            ->filters([
                Filter::make('is_empty')
                    ->form([
                        Checkbox::make('is_empty')
                            ->label('Without Giata ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_empty']) {
                            return $query->with('mapperGiataExpedia')->whereDoesntHave('mapperGiataExpedia', function (Builder $query) {
                                $query->whereNotNull('giata_id');
                            });
                        } else return $query;
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['is_empty']) {
                            return null;
                        }
                        return 'Without Giata ID';
                    }),
                Filter::make('is_multiple')
                    ->form([
                        Checkbox::make('is_multiple')
                            ->label('Multiple Giata ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_multiple']) {
                            return $query->with('mapperGiataExpedia')
                                ->withCount('mapperGiataExpedia')
                                ->has('mapperGiataExpedia', '>');
                        } else return $query;
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['is_multiple']) {
                            return null;
                        }
                        return 'Multiple Giata ID';
                    })
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.expedia-table');
    }
}
