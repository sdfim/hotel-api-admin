<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class BookingItemsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ApiBookingItem::where('rate_type', 'complete')->orderBy('created_at', 'DESC'))
            ->columns([
                TextColumn::make('created_at')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->formatStateUsing(function (ApiBookingItem $record) {
                        return \App\Helpers\TimezoneConverter::convertUtcToEst($record->created_at);
                    }),
                TextColumn::make('search.search_type')
                    ->label('Type')
                    ->numeric()
                    ->icon(fn (ApiBookingItem $record): string => match ($record->search->search_type) {
                        'hotel' => 'heroicon-o-home',
                        'flight' => 'heroicon-o-airplane',
                        default => 'heroicon-o-search',
                    })
                    ->toggleable()
                    ->size(TextColumn\TextColumnSize::Large)
                    ->color(fn (string $state): string => match ($state) {
                        'hotel' => 'grey',
                        'flight' => 'success',
                        default => 'info',
                    })
                    ->searchable(isIndividual: true),
                ViewColumn::make('booking_item')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->view('dashboard.booking-items.column.booking-item'),
                ViewColumn::make('search_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->view('dashboard.booking-items.column.search-id'),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                ViewColumn::make('booking_item_data')
                    ->label('Item data')
                    ->view('dashboard.booking-items.column.booking-item-data'),
                ViewColumn::make('query')
                    ->label('Query')
                    ->view('dashboard.booking-items.column.search'),
                ViewColumn::make('booking_pricing_data')
                    ->label('Pricing data')
                    ->view('dashboard.booking-items.column.booking-pricing-data'),

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
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.booking-items-table');
    }
}
