<?php

namespace App\Livewire;

use App\Models\Reservation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\ImageColumn;
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
            ->paginated([5, 10, 25, 50])
            ->query(Reservation::query()->whereNull('canceled_at')->orderBy('created_at', 'DESC'))
            ->columns([
				ViewColumn::make('reservation_contains')
					->searchable(isIndividual: true)
                    ->view('dashboard.reservations.column.contains'),
				TextColumn::make('channel.name')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable(),
				ViewColumn::make('reservation_contains.hotel_images')
                    ->label('Hotel images')
                    ->view('dashboard.reservations.column.hotel-images', ['limit' => 5]),
                TextColumn::make('date_offload')
                    ->default('N\A')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('date_travel')
                    ->dateTime()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('passenger_surname')
                    ->searchable(isIndividual: true),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable(isIndividual: true)
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
                        ->url(fn(Reservation $record): string => route('reservations.show', $record)),
                    Action::make('Cancel')
                        ->requiresConfirmation()
                        ->action(function (Reservation $record) {
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
