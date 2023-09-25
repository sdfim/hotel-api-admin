<?php

namespace App\Livewire;

use App\Models\Reservations;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ReservationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Reservations::query())
            ->columns([
                Tables\Columns\TextColumn::make('contains_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_offload')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_travel')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('passenger_surname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('canceled_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
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

    public function render(): View
    {
        return view('livewire.reservations-table');
    }
}
