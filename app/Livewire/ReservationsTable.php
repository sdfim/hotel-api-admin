<?php

namespace App\Livewire;

use App\Models\Reservation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class ReservationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
                ImageColumn::make('images')
                    ->state(function (Reservation $record) {
                        $reservationContains = json_decode($record->reservation_contains, true);
                        $images = [];
                        if (isset($reservationContains['hotel_images'])) {
                            $images = json_decode($reservationContains['hotel_images']);
                        }
                        if (isset($reservationContains['flight_images'])) {
                            $images = json_decode($reservationContains['flight_images']);
                        }

                        return $images;
                    })
                    ->circular()
                    ->stacked()
                    ->limit(4)
                    ->size(45)
                    ->limitedRemainingText(isSeparate: true)
                    ->url(fn (Reservation $record): string => route('reservations.show', $record))
                    ->openUrlInNewTab(),
                TextColumn::make('date_offload')
                    ->default('N\A')
                    ->searchable(isIndividual: true)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'N\A' => 'info',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('date_travel')
                    ->date()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('passenger_surname')
                    ->searchable(isIndividual: true),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->weight(FontWeight::Bold)
                    ->size(TextColumn\TextColumnSize::Large)
                    ->searchable(isIndividual: true)
                    ->money(function (Reservation $reservation) {
                        $price = json_decode($reservation->reservation_contains, true)['price'] ?? [];

                        return $price['currency'] ?? 'USD';
                    })
                    ->color(function (Reservation $reservation) {
                        $currency = json_decode($reservation->reservation_contains, true)['price']['currency'] ?? 'USD';

                        return match ($currency) {
                            'EUR' => 'info',
                            'GBP' => 'danger',
                            'CAD' => 'warning',
                            'JPY' => 'gray',
                            default => 'success',
                        };
                    })
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
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (Reservation $record): string => route('reservations.show', $record)),
                    Action::make('Cancel')
                        ->requiresConfirmation()
                        ->action(function (Reservation $record) {
                            $record->update(['canceled_at' => date('Y-m-d H:i:s')]);
                        })
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                        ->visible(fn (Reservation $record): bool => Gate::allows('update', $record)),
                ])->color('gray'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.reservations-table');
    }
}
