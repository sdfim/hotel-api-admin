<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Tables\Columns\TextInputColumn;

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
                    ->searchable(),
                TextColumn::make('name'),
                  //  ->searchable(),
                TextColumn::make('city')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mapperGiataExpedia.giata_id')
                    ->label('Giata id')
                    ->sortable(),
                    
                // TextColumn::make('postal_code')
                //     ->sortable(),
                // TextColumn::make('latitude')
                //     ->sortable(),
                // TextColumn::make('longitude')
                //     ->sortable(),
                // TextColumn::make('address')
                //     ->sortable(),
                // TextColumn::make('phone')
                //     ->sortable(),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                // ActionGroup::make([
                //     ViewAction::make()
                //         ->url(fn(Channels $record): string => route('channels.show', $record))
                //         ->color('info'),
                //     EditAction::make()
                //         ->url(fn(Channels $record): string => route('channels.edit', $record))
                //         ->color('primary'),
                //     DeleteAction::make()
                //         ->requiresConfirmation()
                //         ->action(fn(Channels $record) => $record->delete())
                //         ->color('danger'),
                // ])
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
