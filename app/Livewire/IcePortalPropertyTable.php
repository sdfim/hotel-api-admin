<?php

namespace App\Livewire;

use App\Models\IcePortalPropertyAsset;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Filament\Tables\Actions\ActionGroup;
use Modules\HotelContentRepository\Models\Hotel;

class IcePortalPropertyTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(IcePortalPropertyAsset::query())
            ->columns([
                TextColumn::make('first_mapperHbsiGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperHbsiGiata->first())
                        ->giata_id)
                    ->url(fn ($record) => $record->mapperHbsiGiata->first()
                        ? route(
                            'properties.index',
                            ['giata_id' => optional($record->mapperHbsiGiata->first())->giata_id]
                        )
                        : null)
                    ->toggleable(),
                TextColumn::make('listingID')
                    ->label('Listing ID')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('name')
                    ->wrap()
                    ->html()
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('addressLine1')
                    ->wrap()
                    ->label('Address')
                    ->toggleable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('country')
                    ->wrap()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('postalCode')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('latitude')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(),
                IconColumn::make('has_room_types')
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
            ->filters([
                \Filament\Tables\Filters\Filter::make('giata_code')
                    ->label('Exist in Supplier Repository')
                    ->query(function (Builder $query) {
                        $giataCodes = Hotel::pluck('giata_code');
                        $query->whereHas('mapperHbsiGiata', function (Builder $subQuery) use ($giataCodes) {
                            $subQuery->whereIn('giata_id', $giataCodes);
                        });
                    })
                    ->default(true),
                SelectFilter::make('listingID')
                    ->label('Hotel Code')
                    ->default(fn () => request()->get('supplierHotelCode'))
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search): array {
                        return IcePortalPropertyAsset::query()
                            ->has('mapperHbsiGiata')
                            ->where('listingID', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('listingID', 'listingID')
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn (string $value): ?string => $value),
            ]);
    }

    public function render(): View
    {
        return view('livewire.ice-portal-property-table');
    }
}
