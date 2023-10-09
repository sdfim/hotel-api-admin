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
                TextColumn::make('property_id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable(),
                TextColumn::make('city')
                    ->numeric()
                    ->sortable(),
                ViewColumn::make('address')->view('dashboard.expedia.column.address-field'),
                ViewColumn::make('location')->view('dashboard.expedia.column.position-field'),
                TextColumn::make('mapperGiataExpedia.giata_id')
                    ->label('Giata id'),
                ViewColumn::make('id')->view('dashboard.expedia.column.add-giata'),
            ])
            ->filters([
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['name'],
                                fn(Builder $query, $name): Builder => $query->where('name', 'LIKE', '%' . $name . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['name']) {
                            return null;
                        }
                        return 'Name: ' . $data['name'];
                    }),
                Filter::make('city')
                    ->form([
                        TextInput::make('city')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['city'],
                                fn(Builder $query, $city): Builder => $query->where('city', $city),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['city']) {
                            return null;
                        }
                        return 'City: ' . $data['city'];
                    }),
                Filter::make('address')
                    ->form([
                        TextInput::make('address')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['address'],
                                fn(Builder $query, $address): Builder => $query->where('address', 'LIKE', '%' . $address . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['address']) {
                            return null;
                        }
                        return 'Address: ' . $data['address'];
                    }),
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
