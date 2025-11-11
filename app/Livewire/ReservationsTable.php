<?php

namespace App\Livewire;

use App\Models\Reservation;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Requests\BookingCancelBooking;

class ReservationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Reservation::query()->with('apiBookingsMetadata'))
            ->defaultSort('created_at', 'DESC')
            ->columns([
                ViewColumn::make('reservation_contains')
                    ->searchable(isIndividual: true)
                    ->view('dashboard.reservations.column.contains'),
                TextColumn::make('channel.name')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable(),

                TextColumn::make('apiBookingsMetadata.supplier_booking_item_id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true)
                    ->label('Confirmation')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return implode('<br>', array_map('trim', $state));
                        }
                        if (is_string($state)) {
                            return implode('<br>', array_map('trim', explode(',', $state)));
                        }

                        return '';
                    })
                    ->html(),
                TextColumn::make('apiBookingsMetadata.hotel_supplier_id')
                    ->label('Hotel Id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true),
                TextColumn::make('apiBookingsMetadata')
                    ->label('Hotel/Vendor')
                    ->wrap()
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->formatStateUsing(function ($state, $record) {
                        $hotelName = $record->apiBookingsMetadata?->hotel?->name ?? '';
                        $vendorName = $record->apiBookingsMetadata?->supplier?->name ?? '';

                        return "$hotelName ($vendorName)";
                    }),

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
                TextColumn::make('created_at')
                    ->label('Offload')
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
                    ->toggleable(isToggledHiddenByDefault: true)
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

                TextColumn::make('canceled_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn (Reservation $record) => $record->canceled_at ? 'Canceled' : 'Active')
                    ->badge()
                    ->color(fn ($state) => $state === 'Canceled' ? 'danger' : 'success'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (Reservation $record): string => route('reservations.show', $record)),
                    Action::make('Cancel')
                        ->requiresConfirmation()
                        ->action(function (Reservation $record) {
                            $booking_id = $record->booking_id;
                            $booking_item = $record->booking_item;

                            // Create BookingCancelBooking request
                            $request = new BookingCancelBooking([
                                'booking_id' => $booking_id,
                                'booking_item' => $booking_item,
                            ]);

                            $handler = app(BookApiHandler::class);

                            // Call cancelBooking
                            $response = $handler->cancelBooking($request);
                            $result = $response->getData(true);

                            // Only update canceled_at if cancellation is successful
                            if (isset($result['success']) || (isset($result['result']) && empty($result['error']))) {
                                $record->update(['canceled_at' => date('Y-m-d H:i:s')]);
                            } else {
                                // Optionally, handle error (e.g., flash message)
                            }
                        })
                        ->icon('heroicon-s-x-circle')
                        ->color('danger')
                        ->visible(fn (Reservation $record): bool => Gate::allows('update', $record) && $record->canceled_at === null),
                ])->color('gray'),
            ])
            ->filters([
                SelectFilter::make('cancellation')
                    ->label('Booking Status')
                    ->options([
                        'active' => 'Active',
                        'canceled' => 'Canceled',
                    ])
                    ->default('active')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        $query
                            ->when($value === 'active', fn (Builder $q) => $q->whereNull('canceled_at'))
                            ->when($value === 'canceled', fn (Builder $q) => $q->whereNotNull('canceled_at'));
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.reservations-table');
    }
}
