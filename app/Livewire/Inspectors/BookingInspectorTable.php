<?php

namespace App\Livewire\Inspectors;

use App\Helpers\TimezoneConverter;
use App\Models\ApiBookingInspector;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Requests\BookingCancelBooking;

class BookingInspectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ApiBookingInspector::query()->with(['bookingItem', 'supplier', 'token'])->latest())
            ->defaultSort('created_at', 'DESC')
            ->columns([
                ViewColumn::make('search_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.search-id'),
                ViewColumn::make('booking_item')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.booking-item'),
                TextColumn::make('booking_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.booking-id')
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Endpoint'),
                TextColumn::make('sub_type')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Category'),
                TextColumn::make('status')
                    ->searchable(isIndividual: true)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'error' => 'danger',
                        'success' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('bookingItem.email_verified')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(),
                ViewColumn::make('view error data')
                    ->label('')
                    ->view('dashboard.booking-inspector.column.error-data'),
                ViewColumn::make('request')
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.request'),

                TextColumn::make('metadata.supplier_booking_item_id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true)
                    //                    ->toggleable()
                    ->label('Confirmation'),
                TextColumn::make('metadata.hotel_supplier_id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true)
                    //                    ->toggleable()
                    ->label('Hotel Id'),
                TextColumn::make('metadata')
                    ->label('Hotel/Vendor')
                    ->toggleable()
                    ->toggledHiddenByDefault(true)
                    ->formatStateUsing(function ($state, $record) {
                        return $record->metadata?->hotel?->name ?? '';
                    }),

                TextColumn::make('token.name')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Channel'),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('created_at')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->formatStateUsing(function (ApiBookingInspector $record) {
                        return Carbon::parse(TimezoneConverter::convertUtcToEst($record->created_at))->format('m/d/Y H:i:s');
                    }),
            ])
            ->actions([
                Action::make('Cancel')
                    ->iconButton()
                    ->tooltip('Cancel')
                    ->requiresConfirmation()
                    ->action(function (ApiBookingInspector $record) {
                        $booking_id = $record->booking_id;
                        $booking_item = $record->booking_item;

                        $request = new BookingCancelBooking([
                            'booking_id' => $booking_id,
                            'booking_item' => $booking_item,
                        ]);

                        $handler = app(BookApiHandler::class);

                        // Call cancelBooking
                        $response = $handler->cancelBooking($request);
                    })
                    ->icon('heroicon-s-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (ApiBookingInspector $record): bool => $record->type === 'book' &&
                        $record->sub_type === 'create' &&
                        $record->status === 'success' &&
                        ! ApiBookingInspector::where('booking_id', $record->booking_id)
                            ->where('type', 'cancel_booking')
                            ->where('status', 'success')
                            ->exists()
                    ),
            ])
            ->filters([
                Filter::make('booked_not_cancelled')
                    ->label('Booked but not Cancelled')
                    ->toggle()
                    ->query(function (Builder $query): Builder {
                        return $query
                            ->where('type', 'book')
                            ->where('sub_type', 'create')
                            ->where('status', 'success')
                            ->whereNotIn('booking_id', function ($subQuery) {
                                $subQuery->select('booking_id')
                                    ->from('api_booking_inspector')
                                    ->where('type', 'cancel_booking')
                                    ->where('status', 'success');
                            });
                    }),

                Filter::make('category')
                    ->form([
                        Select::make('category')
                            ->label('Category')
                            ->multiple()
                            ->options([
                                'Book | Basket' => [
                                    'complete|add_item' => 'add_item (quote)',
                                    'add|add_passengers' => 'add_passengers',
                                    'remove_item|remove_item' => 'remove_item',
                                ],
                                'Book | Booking' => [
                                    'retrieve|book' => 'retrieve_book',
                                    'create|book' => 'book',
                                ],
                                'Change' => [
                                    'change|change_passengers' => 'change_passengers',
                                    'change-hard|change_book' => 'change_book_hard',
                                    'change-soft|change_book' => 'change_book_soft',
                                    'update_change|change_passengers' => 'change_passengers_update',
                                    'price-check|change' => 'change_price-check',
                                ],
                                'Cancel' => [
                                    'true|cancel_booking' => 'cancel_booking',
                                ],
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['category']) && is_array($data['category'])) {
                            $query->where(function ($query) use ($data) {
                                foreach ($data['category'] as $cat) {
                                    [$subType, $type] = explode('|', $cat);
                                    $query->orWhere(function ($q) use ($subType, $type) {
                                        $q->where('sub_type', trim($subType))->where('type', trim($type));
                                    });
                                }
                            });
                        } elseif (! empty($data['category'])) {
                            [$subType, $type] = explode('|', $data['category']);
                            $query->where('sub_type', trim($subType))->where('type', trim($type));
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data) {
                        $map = [
                            'add|add_passengers' => 'add_passengers',
                            'remove_item|remove_item' => 'remove_item',
                            'complete|add_item' => 'add_item',
                            'create|book' => 'book',
                            'true|cancel_booking' => 'cancel_booking',
                            'retrieve|book' => 'retrieve_book',
                            'price-check|change' => 'change_price-check',
                            'change|change_passengers' => 'change_passengers',
                            'change-hard|change_book' => 'change_book_hard',
                            'change-soft|change_book' => 'change_book_soft',
                            'update_change|change_passengers' => 'change_passengers_update',
                        ];
                        if (! empty($data['category']) && is_array($data['category'])) {
                            $labels = array_map(fn ($cat) => $map[$cat] ?? $cat, $data['category']);

                            return 'Category: '.implode(', ', $labels);
                        } elseif (! empty($data['category'])) {
                            return 'Category: '.($map[$data['category']] ?? $data['category']);
                        }

                        return null;
                    }),
                Filter::make('created_at')
                    ->form([
                        DateTimePicker::make('created_from'),
                        DateTimePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('is_booked')
                    ->form([
                        Select::make('is_book')
                            ->label('Select a Status')
                            ->options([
                                'booked' => 'Booked',
                                'not_booked' => 'Not Booked',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        switch ($data['is_book']) {
                            case 'booked':
                                return $query->whereIn('booking_id', function ($subQuery) {
                                    $subQuery->select('booking_id')
                                        ->from('api_booking_inspector')
                                        ->where('type', 'book')
                                        ->distinct();
                                });
                            case 'not_booked':
                                return $query->whereNotIn('booking_id', function ($subQuery) {
                                    $subQuery->select('booking_id')
                                        ->from('api_booking_inspector')
                                        ->where('type', 'book')
                                        ->distinct();
                                });
                            default:
                                return $query;
                        }
                    })->indicateUsing(function (array $data): ?string {
                        switch ($data['is_book']) {
                            case 'booked':
                                return 'Booked Status';
                            case 'not_booked':
                                return 'Not Booked Status';
                            default:
                                return null;
                        }
                    }),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ApiBookingInspector::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => config('superuser.email') === auth()->user()->email),
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.booking-inspector-table');
    }
}
