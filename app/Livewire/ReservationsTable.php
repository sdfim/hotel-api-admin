<?php

namespace App\Livewire;

use App\Models\Reservations;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class ReservationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Reservations::query()->whereNull('canceled_at'))
            ->columns([
                ViewColumn::make('contains.name')
                    ->searchable()
                    ->view('components.datatable-contains-column'),
                TextColumn::make('channel.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_offload')
                    ->default('N\A')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_travel')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('passenger_surname')
                    ->searchable(),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->searchable()
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('canceled_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(Reservations $record): string => route('reservations.show', $record)),
                    Action::make('Cancel')
                        ->requiresConfirmation()
                        ->action(function (Reservations $record) {
                            $record->update(['canceled_at' => date('Y-m-d H:i:s')]);
                        })
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                ])->color('gray'),
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
        return view('livewire.reservations-table');
    }
}
