<?php

namespace App\Livewire;

use App\Models\PropertyWeighting;
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

class WeightsTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(PropertyWeighting::query())
            ->columns([
                TextColumn::make('property')
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('weight')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(PropertyWeighting $record): string => route('weight.show', $record)),
                    EditAction::make()
                        ->url(fn(PropertyWeighting $record): string => route('weight.edit', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn(PropertyWeighting $record) => $record->delete())
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.weights-table');
    }
}
