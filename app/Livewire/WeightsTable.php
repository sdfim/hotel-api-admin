<?php

namespace App\Livewire;

use App\Models\Weights;
use Illuminate\View\View;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;

class WeightsTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table (Table $table): Table
    {
        return $table
            ->query(Weights::query())
            ->columns([
                Tables\Columns\TextColumn::make('property')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(Weights $record): string => route('weight.show', $record))
                        ->color('info'),
                    EditAction::make()
                        ->url(fn(Weights $record): string => route('weight.edit', $record))
                        ->color('primary'),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(Weights $record) => $record->delete())
                        ->color('danger'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.weights-table');
    }
}
