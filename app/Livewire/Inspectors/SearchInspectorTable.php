<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiSearchInspector;
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

class SearchInspectorTable extends Component implements HasForms, HasTable
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
            ->query(ApiSearchInspector::orderBy('created_at', 'DESC'))
            ->columns([
				ViewColumn::make('search_id')
					->tooltip('view Search ID data')
					->searchable(isIndividual: true)
					->view('dashboard.search-inspector.column.search-id'),
				ViewColumn::make('request')
					->toggleable()
					->searchable(isIndividual: true)
					->view('dashboard.search-inspector.column.request-data'),
				ViewColumn::make('request json')
					->label('')
					->view('dashboard.search-inspector.column.request'),
                TextColumn::make('token.name')
					->label('Channel')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('suppliers')
                    ->toggleable()
                    ->formatStateUsing(function (ApiSearchInspector $record): string {
                        $suppliers_name_string = '';
                        $suppliers_array = explode(',', $record->suppliers);
                        for ($i = 0; $i < count($suppliers_array); $i++) {
                            $supplier = Supplier::find($suppliers_array[$i]);
                            $suppliers_name_string .= $supplier->name . ', ';
                        }
                        // remove all spaces and commas from the end of the line if present
                        return rtrim($suppliers_name_string, " ,");
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereIn('suppliers', explode(',', $search));
                    }),
				TextColumn::make('created_at')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
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
        return view('livewire.inspectors.search-inspector-table');
    }
}
