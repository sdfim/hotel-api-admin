<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingInspector;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

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
            ->query(ApiBookingInspector::orderBy('created_at', 'DESC'))
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
                ViewColumn::make('view error data')
                    ->label('')
                    ->view('dashboard.booking-inspector.column.error-data'),
                ViewColumn::make('request')
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.request'),
                TextColumn::make('metadata.supplier_booking_item_id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('code booking'),
                TextColumn::make('metadata.hotel_supplier_id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('hotel id'),
                TextColumn::make('token.id')
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
                        return \App\Helpers\TimezoneConverter::convertUtcToEst($record->created_at);
                    }),
            ])
            ->actions([
                //                ActionGroup::make([
                //                    ViewAction::make()
                //                        ->url(fn(ApiBookingInspector $record): string => route('booking-inspector.show', $record))
                //                        ->label('View response')
                //                        ->color('info')
                //                ])
            ])
            ->filters([
                Filter::make('is_book')
                    ->form([
                        Checkbox::make('is_book')
                            ->label('Is Book Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_book']) {
                            return $query->whereIn('booking_id', function ($subQuery) {
                                $subQuery->select('booking_id')
                                    ->from('api_booking_inspector')
                                    ->where('type', 'book')
                                    ->distinct();
                            });
                        } else {
                            return $query;
                        }
                    })->indicateUsing(function (array $data): ?string {
                        if (! $data['is_book']) {
                            return null;
                        }

                        return 'Book Status';
                    }),
                Filter::make('is_not_book')
                    ->form([
                        Checkbox::make('is_not_book')
                            ->label('Is NOT Book Status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_not_book']) {
                            return $query->whereNotIn('booking_id', function ($subQuery) {
                                $subQuery->select('booking_id')
                                    ->from('api_booking_inspector')
                                    ->where('type', 'book')
                                    ->distinct();
                            });
                        } else {
                            return $query;
                        }
                    })->indicateUsing(function (array $data): ?string {
                        if (! $data['is_not_book']) {
                            return null;
                        }

                        return 'NOT Book Status';
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.booking-inspector-table');
    }
}
