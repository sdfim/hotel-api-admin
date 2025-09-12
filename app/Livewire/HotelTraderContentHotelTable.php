<?php

namespace App\Livewire;

use App\Models\HotelTraderProperty;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class HotelTraderContentHotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(HotelTraderProperty::query())
            ->columns([
                TextColumn::make('first_mapperHbsiGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperHbsiGiata->first())->giata_id)
                    ->url(fn ($record) => $record->mapperHbsiGiata->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperHbsiGiata->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('propertyId')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('propertyName')
                    ->sortable()
                    ->wrap()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('city')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('countryCode')
                    ->label('Country')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('starRating')
                    ->label('Rating')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('address1')
                    ->label('Address')
                    ->wrap()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('phone1')
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('latitude')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('longitude')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
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
            ]);
    }

    public function render(): View
    {
        return view('livewire.hotel-trader-content-table');
    }
}
