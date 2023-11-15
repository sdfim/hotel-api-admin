<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingItem;
use App\Models\Supplier;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class BookingItemsTable extends Component implements HasForms, HasTable
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
            ->query(ApiBookingItem::orderBy('created_at', 'DESC'))
            ->columns([
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
                ViewColumn::make('booking_pricing_data')
					->label('Pricing data')
					->view('dashboard.booking-items.column.booking-pricing-data'),
                
				])
            ->filters([])
            // ->actions([
            //     ViewAction::make()
            //         ->url(fn(ApiSearchInspector $record): string => route('search-inspector.show', $record))
            //         ->label('View response')
            //         ->color('info'),

            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([]),
            // ])
			;
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.inspectors.booking-items-table');
    }
}
