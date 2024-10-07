<?php

namespace App\Livewire\Inspectors;

use App\Helpers\TimezoneConverter;
use App\Models\ApiBookingInspector;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
                    ->label('Confirmation Number'),
                TextColumn::make('metadata.hotel_supplier_id')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Hotel Id'),
                ViewColumn::make('metadata')
                    ->label('Hotel/Vendor')
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.hotel-name'),
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
                //                ActionGroup::make([
                //                    ViewAction::make()
                //                        ->url(fn(ApiBookingInspector $record): string => route('booking-inspector.show', $record))
                //                        ->label('View response')
                //                        ->color('info')
                //                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->delete()),
                    BulkAction::make('forceDelete')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->forceDelete()),
                ]),
            ])
            ->filters([
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
                            ])
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
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.booking-inspector-table');
    }
}
