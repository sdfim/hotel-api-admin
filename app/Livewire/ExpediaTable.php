<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Tables\Columns\ViewColumn;

class ExpediaTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table (Table $table): Table
    {
        ini_set('memory_limit', '1586M');

        return $table
            ->query(ExpediaContent::query())
            ->columns([
                TextColumn::make('property_id')
                ->sortable()
                ->searchable(),
                TextColumn::make('name')
                ->sortable(),
                TextColumn::make('city')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mapperGiataExpedia.giata_id')
                    ->label('Giata id'),
                ViewColumn::make('id')->view('dashboard.expedia.column.add-giata'),                
            ])
            ->filters([
                //
            ])
            ->actions([
               //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.expedia-table');
    }
}
