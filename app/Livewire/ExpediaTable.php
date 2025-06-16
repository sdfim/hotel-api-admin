<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Exception;
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
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ExpediaTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10])
            ->query(ExpediaContent::query())
            ->defaultSort('rating', 'desc')
            ->columns([
                TextColumn::make('property_id')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('property_id', $search);
                    }, isIndividual: true)
                    ->toggleable(),
                ViewColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->view('dashboard.expedia.column.name-field'),
                TextColumn::make('rating')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                ViewColumn::make('address')
                    ->view('dashboard.expedia.column.address-field')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->searchable(isIndividual: true)
                    ->label('Active')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')
                        ->fromTable()
                        ->withFilename('properties_export_'.now()->format('Y_m_d_H_i_s').'.xlsx'),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.expedia-table');
    }
}
