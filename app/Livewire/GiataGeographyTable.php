<?php

namespace App\Livewire;

use App\Models\GiataGeography;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class GiataGeographyTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(GiataGeography::query())
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('city_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('city_name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('locale_id')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->sortable(),
                TextColumn::make('locale_name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('country_code')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('country_name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.giata-geography-table');
    }
}
