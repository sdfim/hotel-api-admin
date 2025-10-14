<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
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
            ->query(ExpediaContent::query()->with('expediaSlave'))
            ->defaultSort('rating', 'desc')
            ->columns([
                TextColumn::make('first_mapperGiataExpedia_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperGiataExpedia->first())->giata_id)->url(fn ($record) => $record->mapperGiataExpedia->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperGiataExpedia->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('property_id')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                ViewColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->view('dashboard.expedia.column.name-field'),
                TextColumn::make('rating')
                    ->numeric()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('rating', $search);
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                ViewColumn::make('address')
                    ->view('dashboard.expedia.column.address-field')
                    ->toggleable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View')
                        ->modalWidth('7xl')
                        ->icon('heroicon-o-eye')
                        ->modalHeading('Property Details')
                        ->modalDescription(fn ($record) => $record->name)
                        ->modalContent(fn ($record) => view('livewire.modal.property-view', ['record' => $record])),
                ]),
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
