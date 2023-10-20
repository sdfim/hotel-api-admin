<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Filament\Forms\Components\TextInput;
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

    public function table (Table $table): Table
    {
        ini_set('memory_limit', '1586M');

        return $table
            ->query(ExpediaContent::query())
            ->columns([
				TextColumn::make('id')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('property_id')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('city')
                    ->numeric()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
				TextColumn::make('phone')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                ViewColumn::make('address')->view('dashboard.expedia.column.address-field')
                ->searchable(isIndividual: true)
                ->toggleable(),
                ViewColumn::make('location')->view('dashboard.expedia.column.position-field')
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
                    if(count($record->mapperGiataExpedia) > 1){
                        return 'Multivariate';
                    }else if(count($record->mapperGiataExpedia) == 1){
                        return 'Single';
                    }
                    return 'Empty';
                })
                ->toggleable(),
				TextColumn::make('mapperGiataExpedia.step')
                ->searchable(isIndividual: true)
                ->label('Version')
                ->formatStateUsing(function ($record) {
                    if(count($record->mapperGiataExpedia) > 1){
                        return 'Autom';
                    }
                    if(count($record->mapperGiataExpedia) == 1){
                        if($record->mapperGiataExpedia[0]->step == 100){
                            return 'Manual';
                        }else{
                            return 'Auto';
                        }
                    }
                })
                ->toggleable(),
                ViewColumn::make('edit')->view('dashboard.expedia.column.add-giata')->toggleable(isToggledHiddenByDefault: false),
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
                            ->has('mapperGiataExpedia', '>', 1);
						} else return $query;
					})->indicateUsing(function (array $data): ?string {
						if (!$data['is_multiple']) {
							return null;
						}
						return 'Multiple Giata ID';
					}),
                // Filter::make('name')
                //     ->form([
                //         TextInput::make('name')
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['name'],
                //                 fn(Builder $query, $name): Builder => $query->where('name', 'LIKE', '%' . $name . '%'),
                //             );
                //     })->indicateUsing(function (array $data): ?string {
                //         if (!$data['name']) {
                //             return null;
                //         }
                //         return 'Name: ' . $data['name'];
                //     }),
                // Filter::make('city')
                //     ->form([
                //         TextInput::make('city')
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['city'],
                //                 fn(Builder $query, $city): Builder => $query->where('city', $city),
                //             );
                //     })->indicateUsing(function (array $data): ?string {
                //         if (!$data['city']) {
                //             return null;
                //         }
                //         return 'City: ' . $data['city'];
                //     }),
                // Filter::make('address')
                //     ->form([
                //         TextInput::make('address')
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['address'],
                //                 fn(Builder $query, $address): Builder => $query->where('address', 'LIKE', '%' . $address . '%'),
                //             );
                //     })->indicateUsing(function (array $data): ?string {
                //         if (!$data['address']) {
                //             return null;
                //         }
                //         return 'Address: ' . $data['address'];
                //     }),
                // Filter::make('giata_id')
                // ->form([
                //     Checkbox::make('giata_id')
                //     ->label('With Giata ID')

                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['giata_id'],
                //             fn (Builder $query, $giata_id): Builder => $query->leftJoin('mapper_expedia_giatas', 'expedia_contents.property_id', '=', 'mapper_expedia_giatas.expedia_id')->whereNotNull('mapper_expedia_giatas.giata_id'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (! $data['giata_id']) {
                //         return null;
                //     }
                //     return 'With Giata ID';
                // }),
                // Filter::make('property_id')
                // ->label('Without Giata ID')
                // ->form([
                //     Checkbox::make('giata_id')

                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['giata_id'],
                //             fn (Builder $query, $giata_id): Builder => $query->leftJoin('mapper_expedia_giatas', 'expedia_contents.property_id', '=', 'mapper_expedia_giatas.expedia_id')->whereNull('mapper_expedia_giatas.giata_id'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (! $data['giata_id']) {
                //         return null;
                //     }
                //     return 'With Giata ID';
                // }),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.expedia-table');
    }
}
