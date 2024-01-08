<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingInspector;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Checkbox;
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
use Livewire\Component;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;

class BookingInspectorTable extends Component implements HasForms, HasTable
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
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Endpoint'),
                TextColumn::make('sub_type')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Step'),
                TextColumn::make('token.id')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->label('Channel'),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                ViewColumn::make('request')
                    ->toggleable()
                    ->view('dashboard.booking-inspector.column.request'),
                TextColumn::make('created_at')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(ApiBookingInspector $record): string => route('booking-inspector.show', $record))
                        ->label('View response')
                        ->color('info')
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->filters([
                Filter::make('is_book')
                    ->form([
                        Checkbox::make('is_book')
                            ->label('Is Book Status')
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
                        if (!$data['is_book']) {
                            return null;
                        }
                        return 'Book Status';
                    }),
                Filter::make('is_not_book')
                    ->form([
                        Checkbox::make('is_not_book')
                            ->label('Is NOT Book Status')
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
                        if (!$data['is_not_book']) {
                            return null;
                        }
                        return 'NOT Book Status';
                    })
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.inspectors.booking-inspector-table');
    }
}
