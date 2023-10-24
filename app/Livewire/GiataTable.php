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
                    ->searchable(isIndividual: true),
                TextColumn::make('name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('city')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('locale')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                ViewColumn::make('phone')->toggleable()->view('dashboard.giata.column.phone-field')->searchable(isIndividual: true),
                ViewColumn::make('address')->toggleable()->view('dashboard.giata.column.address-field')->searchable(isIndividual: true),
                ViewColumn::make('position')->toggleable()->view('dashboard.giata.column.position-field')->searchable(isIndividual: true),
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
                                fn(Builder $query, $city): Builder => $query->where('city', 'LIKE', '%' . $city . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['city']) {
                            return null;
                        }
                        return 'City: ' . $data['city'];
                    }),
                Filter::make('locale')
                    ->form([
                        TextInput::make('locale')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['locale'],
                                fn(Builder $query, $city): Builder => $query->where('locale', 'LIKE', '%' . $city . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['locale']) {
                            return null;
                        }
                        return 'Locale: ' . $data['locale'];
                    }),
                Filter::make('phone')
                    ->form([
                        TextInput::make('phone')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['phone'],
                                fn(Builder $query, $phone): Builder => $query->where('phone', 'LIKE', '%' . $phone . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['phone']) {
                            return null;
                        }
                        return 'Phone: ' . $data['phone'];
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
                Filter::make('latitude')
                    ->form([
                        TextInput::make('latitude')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['latitude'],
                                fn(Builder $query, $latitude): Builder => $query->where('position', 'LIKE', '%"' . $latitude . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['latitude']) {
                            return null;
                        }
                        return 'Latitude: ' . $data['latitude'];
                    }),
                Filter::make('longitude')
                    ->form([
                        TextInput::make('longitude')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['longitude'],
                                fn(Builder $query, $longitude): Builder => $query->where('position', 'LIKE', '%"' . $longitude . '%'),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['longitude']) {
                            return null;
                        }
                        return 'Longitude: ' . $data['longitude'];
                    })
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn(GiataProperty $record): string => route('giata.show', $record->code))
                    ->color('info'),
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
