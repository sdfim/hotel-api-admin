<?php

namespace App\Livewire;

use App\Models\HbsiProperty;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

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
                    ->searchable(isIndividual: true),
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
                    ->searchable(isIndividual: true),
                TextColumn::make('postal_code')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('country_name')
                    ->label('Country')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('emails')
                    ->wrap()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                IconColumn::make('has_rate_plans')
                    ->label('Rate Plans')
                    ->boolean(),
                IconColumn::make('has_room_types')
                    ->label('Room Types')
                    ->boolean(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hbsi-property-table');
    }
}
