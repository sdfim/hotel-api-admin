<?php

namespace App\Livewire;

use App\Models\GiataGeography;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;

class GiataGeographyTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table (Table $table): Table
    {
        return $table
            ->query(GiataGeography::query())
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('city_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('city_name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('locale_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->sortable(),
                TextColumn::make('locale_name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('country_code')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('country_name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                // Filter::make('city_name')
                // ->form([
                //     TextInput::make('city_name')
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['city_name'],
                //             fn(Builder $query, $name): Builder => $query->where('city_name', 'LIKE', '%' . $name . '%'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (!$data['city_name']) {
                //         return null;
                //     }
                //     return 'City name: ' . $data['city_name'];
                // }),
                // Filter::make('locale_name')
                // ->form([
                //     TextInput::make('locale_name')
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['locale_name'],
                //             fn(Builder $query, $name): Builder => $query->where('locale_name', 'LIKE', '%' . $name . '%'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (!$data['locale_name']) {
                //         return null;
                //     }
                //     return 'Locale name: ' . $data['locale_name'];
                // }),
                // Filter::make('country_name')
                // ->form([
                //     TextInput::make('country_name')
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['country_name'],
                //             fn(Builder $query, $name): Builder => $query->where('country_name', 'LIKE', '%' . $name . '%'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (!$data['country_name']) {
                //         return null;
                //     }
                //     return 'Country name: ' . $data['country_name'];
                // }),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.giata-geography-table');
    }
}
