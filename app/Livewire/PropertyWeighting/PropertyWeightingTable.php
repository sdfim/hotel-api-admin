<?php

namespace App\Livewire\PropertyWeighting;

use App\Models\Weights;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
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
    use InteractsWithTable;
    use InteractsWithForms;

    public function table (Table $table): Table
    {
        return $table
            ->query(Weights::query())
            ->columns([
                TextColumn::make('property')
                    ->label('Property code')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('giataProperties.name')
                    ->label('Property name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('suppliers.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('weight')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(Weights $record): string => route('weight.show', $record)),
                    EditAction::make()
                        ->url(fn(Weights $record): string => route('weight.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(Weights $record) => $record->delete())
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.property-weighting.property-weighting-table');
    }
}
