<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class SearchInspectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
                TextColumn::make('status')
//                    ->searchable(isIndividual: true)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'error' => 'danger',
                        'success' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('search_type')
                    ->label('Search Type'),
                TextColumn::make('destination_name')
                    ->label('Destination Name'),
                ViewColumn::make('view error data')
                    ->label('')
                    ->view('dashboard.search-inspector.column.error-data'),
                ViewColumn::make('request json')
                    ->label('')
                    ->view('dashboard.search-inspector.column.request'),
                ViewColumn::make('request')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->view('dashboard.search-inspector.column.request-data'),
                TextColumn::make('token.name')
                    ->label('Channel')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('suppliers')
                    ->toggleable()
                    ->formatStateUsing(function (ApiSearchInspector $record): string {
                        return Supplier::whereIn('id', explode(',', $record->suppliers))->pluck('name')->implode(', ');
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereIn('suppliers', explode(',', $search));
                    }),
                TextColumn::make('created_at')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->formatStateUsing(function (ApiSearchInspector $record) {
                        return \App\Helpers\TimezoneConverter::convertUtcToEst($record->created_at);
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.inspectors.search-inspector-table');
    }
}
