<?php

namespace App\Livewire;

use App\Jobs\HiltonImportJob;
use App\Models\HiltonProperty;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;

class HiltonPropertyTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(HiltonProperty::query()->with('secondary'))
            ->columns([
                TextColumn::make('first_mapperHiltonGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperHiltonGiata->first())->giata_id)
                    ->url(fn ($record) => $record->mapperHiltonGiata->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperHiltonGiata->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('prop_code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('name')
                    ->wrap()
                    ->label('Name')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('city')
                    ->label('City')
                    ->wrap()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('country_code')
                    ->label('Country')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('address')
                    ->wrap()->label('Address')
                    ->toggleable()->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('postal_code')
                    ->label('Postal Code')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('latitude')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone_number')
                    ->label('Phone')
                    ->toggleable(),
                TextColumn::make('star_rating')
                    ->label('Stars')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('has_props')
                    ->label('Room Types')
                    ->boolean(),
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
                Action::make('importXls')
                    ->label('Import XLS with T&F and Alerts')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        FileUpload::make('xls_file')
                            ->label('XLS/XLSX File')
                            ->disk(config('filament.default_filesystem_disk', 'public'))
                            ->required()
                            ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->directory('hilton-imports')
                            ->visible()
                            ->visibility('public'),
                    ])
                    ->action(function (array $data) {
                        $filePath = $data['xls_file'] ?? null;
                        if ($filePath) {
                            HiltonImportJob::dispatch($filePath, auth()->user());

                            Notification::make()
                                ->title('Import started')
                                ->body('The import has started. It is queued and may take up to 10 minutes to complete.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import error')
                                ->body('No file uploaded.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('giata_code')
                    ->label('Exist in Supplier Repository')
                    ->query(function (Builder $query) {
                        $giataCodes = Hotel::pluck('giata_code');
                        $query->whereHas('mapperHiltonGiata', function (Builder $subQuery) use ($giataCodes) {
                            $subQuery->whereIn('giata_id', $giataCodes);
                        });
                    })
                    ->default(true),
                SelectFilter::make('prop_code')
                    ->label('Hotel Code')
                    ->default(fn () => request()->get('supplierHotelCode'))
                    ->searchable()
                    ->options(
                        fn () => HiltonProperty::query()
                            ->orderBy('prop_code')
                            ->pluck('prop_code', 'prop_code')
                            ->toArray()
                    ),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hilton-property-table');
    }
}
