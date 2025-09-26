<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiSearchInspector;
use App\Models\Property;
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
            ->query(ApiSearchInspector::query())
            ->defaultSort('created_at', 'DESC')
            ->columns([
                ViewColumn::make('search_id')
                    ->tooltip('view Search ID data')
                    ->searchable(isIndividual: true)
                    ->view('dashboard.search-inspector.column.search-id'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'error' => 'danger',
                        'success' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('search_type')
                    ->label('Type'),
                TextColumn::make('type')
                    ->label('')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'price' => 'success',
                        'check_quote' => 'warning',
                        'change' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('request')
                    ->label('Destination')
                    ->wrap()
                    ->width(300)
                    ->formatStateUsing(function ($state, $record) {
                        $giataIds = json_decode($record->request, true)['giata_ids'] ?? [];
                        if (! is_array($giataIds) || empty($giataIds)) {
                            return '';
                        }
                        $perPage = request('tableRecordsPerPage') ?? request('perPage') ?? 10;
                        $codes = array_slice($giataIds, 0, 3);
                        $properties = Property::whereIn('code', $codes)->pluck('name', 'code')->toArray();
                        $result = [];
                        foreach ($codes as $code) {
                            $name = $properties[$code] ?? '';
                            $result[] = $name ? ("$code | $name ") : $code;
                        }
                        return implode(', ', $result);
                    }),
                ViewColumn::make('view error data')
                    ->label('')
                    ->view('dashboard.search-inspector.column.error-data'),
                ViewColumn::make('request json')
                    ->label('')
                    ->view('dashboard.search-inspector.column.request'),
                ViewColumn::make('request rooms')
                    ->label('Occupancy')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->view('dashboard.search-inspector.column.request-data'),
                TextColumn::make('token.name')
                    ->label('Channel')
                    ->numeric()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('suppliers')
                    ->wrap()
                    ->width(200)
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
