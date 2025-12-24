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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
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
                TextColumn::make('first_mapperGiataExpedia_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperGiataExpedia->first())->giata_id)->url(fn (
                        $record
                    ) => $record->mapperGiataExpedia->first()
                        ? route(
                            'properties.index',
                            ['giata_id' => optional($record->mapperGiataExpedia->first())->giata_id]
                        )
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
                ViewColumn::make('address')
                    ->searchable(isIndividual: true)
                    ->view('dashboard.expedia.column.address-field')
                    ->toggleable(),
                TextColumn::make('city')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('phone')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('rating')
                    ->numeric()
                    ->sortable()
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
                        ->modalContent(fn ($record) => view('livewire.modal.property-view', ['record' => $record->load('expediaSlave')])),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')
                        ->fromTable()
                        ->withFilename('properties_export_'.now()->format('Y_m_d_H_i_s').'.xlsx'),
                ]),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('giata_code')
                    ->label('Exist in Supplier Repository')
                    ->query(function (Builder $query) {
                        $giataCodes = Hotel::pluck('giata_code');
                        $query->whereHas('mapperGiataExpedia', function (Builder $subQuery) use ($giataCodes) {
                            $subQuery->whereIn('giata_id', $giataCodes);
                        });
                    })
                    ->default(true),
                SelectFilter::make('property_id')
                    ->label('Hotel Code')
                    ->default(fn () => request()->get('supplierHotelCode'))
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        return ExpediaContent::query()
                            ->has('mapperGiataExpedia')
                            ->where('property_id', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('property_id', 'property_id')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn (string $value): ?string => $value),
            ]);
    }

    public function render(): View
    {
        return view('livewire.expedia-table');
    }
}
