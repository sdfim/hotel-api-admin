<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiSearchInspector;
use App\Models\Suppliers;
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
            ->query(ApiSearchInspector::orderBy('created_at', 'DESC'))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('search_id')
                    ->searchable()
                    ->toggleable()
                    ->label('Search ID'),
                TextColumn::make('type')
                    ->searchable()
                    ->toggleable()
                    ->label('Endpoint'),
                TextColumn::make('token.id')
                    ->numeric()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('suppliers')
                    ->toggleable()
                    ->formatStateUsing(function (ApiSearchInspector $record): string {
                        $suppliers_name_string = '';
                        $suppliers_array = explode(',', $record->suppliers);
                        for ($i = 0; $i < count($suppliers_array); $i++) {
                            $supplier = Suppliers::find($suppliers_array[$i]);
                            $suppliers_name_string .= $supplier->name . ', ';
                        }
                        // remove all spaces and commas from the end of the line if present
                        return rtrim($suppliers_name_string, " ,");
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereIn('suppliers', explode(',', $search));
                    }),
                ViewColumn::make('request')->toggleable()->view('dashboard.search-inspector.column.request'),
                TextColumn::make('created_at')
                    ->toggleable()
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([])
            ->actions([
                ViewAction::make()
                    ->url(fn(ApiSearchInspector $record): string => route('search-inspector.show', $record))
                    ->label('View response')
                    ->color('info'),

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
        return view('livewire.inspectors.search-inspector-table');
    }
}
