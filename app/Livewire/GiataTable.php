<?php

namespace App\Livewire;

use App\Models\GiataProperty;
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
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\ViewColumn;

class GiataTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table (Table $table): Table
    {
        return $table
            ->query(GiataProperty::query())
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('locale')
                    ->searchable(),
                ViewColumn::make('address')->view('dashboard.giata.column.address-field'),
                ViewColumn::make('phone')->view('dashboard.giata.column.phone-field'),
                ViewColumn::make('position')->view('dashboard.giata.column.position-field'),

                // TextColumn::make('address')
                //     ->sortable(),
                // TextColumn::make('phone')
                //     ->sortable(),
                // TextColumn::make('position')
                //     ->sortable(),
               
            ])
            ->filters([
               
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
        return view('livewire.giata-table');
    }
}
