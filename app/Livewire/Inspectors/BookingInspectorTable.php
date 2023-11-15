<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingInspector;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;
use Filament\Tables\Actions\ViewAction;

class BookingInspectorTable extends Component implements HasForms, HasTable
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
                ViewAction::make()
                    ->url(fn(ApiBookingInspector $record): string => route('booking-inspector.show', $record))
                    ->label('View response')
                    ->color('info')
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
        return view('livewire.inspectors.booking-inspector-table');
    }
}
