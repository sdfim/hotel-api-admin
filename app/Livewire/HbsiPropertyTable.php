<?php

namespace App\Livewire;

use App\Models\HbsiProperty;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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

class HbsiPropertyTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(HbsiProperty::query())
            ->columns([
                TextColumn::make('first_mapperHbsiGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperHbsiGiata->first())->giata_id)
                    ->url(fn ($record) => $record->mapperHbsiGiata->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperHbsiGiata->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('hotel_code')
                    ->label('Code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('hotel_code', $search);
                    }, isIndividual: true),
                TextColumn::make('hotel_name')
                    ->label('Hotel Name')
                    ->wrap()
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('city_code')
                    ->label('City Code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('address_line')
                    ->label('Address')
                    ->wrap()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('city_name')
                    ->label('City')
                    ->wrap()
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('state')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('country_name')
                    ->label('Country')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(),
                IconColumn::make('has_rate_plans')
                    ->label('Rate Plans')
                    ->boolean(),
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
                SelectFilter::make('hotel_code')
                    ->label('Hotel Code')
                    ->default(fn () => request()->get('supplierHotelCode'))
                    ->searchable()
                    ->options(
                        fn () => HbsiProperty::query()
                            ->orderBy('hotel_code')
                            ->pluck('hotel_code', 'hotel_code')
                            ->toArray()
                    ),
                SelectFilter::make('has_room_types')
                    ->label('Has Room Types')
                    ->options([
                        '1' => 'Yes (more than 1)',
                        '0' => 'No (0 or 1)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === '1') {
                            $query->whereRaw('
                    JSON_LENGTH(
                        JSON_EXTRACT(
                            tpa_extensions,
                            "$.InterfaceSetup"
                        )
                    ) > 0
                ')->whereRaw('
                    (
                        SELECT COUNT(*)
                        FROM JSON_TABLE(
                            JSON_EXTRACT(tpa_extensions, "$.InterfaceSetup"),
                            "$[*]" COLUMNS (key_path VARCHAR(100) PATH "$.key")
                        ) AS jt
                        WHERE jt.key_path = "Mapping_Roomtype"
                    ) > 1
                ');
                        } elseif ($data['value'] === '0') {
                            $query->whereRaw('
                    (
                        SELECT COUNT(*)
                        FROM JSON_TABLE(
                            JSON_EXTRACT(tpa_extensions, "$.InterfaceSetup"),
                            "$[*]" COLUMNS (key_path VARCHAR(100) PATH "$.key")
                        ) AS jt
                        WHERE jt.key_path = "Mapping_Roomtype"
                    ) <= 1
                ');
                        }
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hbsi-property-table');
    }
}
