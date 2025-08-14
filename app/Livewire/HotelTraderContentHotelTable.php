<?php

namespace App\Livewire;

use App\Models\HotelTraderProperty;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('starRating')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('address1')
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
            ]);
    }

    public function render(): View
    {
        return view('livewire.hotel-trader-content-table');
    }
}
