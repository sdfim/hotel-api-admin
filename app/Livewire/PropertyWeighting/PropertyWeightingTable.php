<?php

namespace App\Livewire\PropertyWeighting;

use App\Models\PropertyWeighting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class PropertyWeightingTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(PropertyWeighting::query())
            ->columns([
                TextColumn::make('property')
                    ->label('Property code')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('properties.name')
                    ->label('Property name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('weight')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (PropertyWeighting $record): string => route('property-weighting.show', $record)),
                    EditAction::make()
                        ->url(fn (PropertyWeighting $record): string => route('property-weighting.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (PropertyWeighting $record) => $record->delete()),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.property-weighting.property-weighting-table');
    }
}
