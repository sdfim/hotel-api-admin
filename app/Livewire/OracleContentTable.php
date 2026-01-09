<?php

namespace App\Livewire;

use App\Models\OracleContent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;

class OracleContentTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(OracleContent::query())
            ->columns([
                TextColumn::make('first_mapperHbsiGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperOracleGiata->first())
                        ->giata_id)
                    ->url(fn ($record) => $record->mapperOracleGiata->first()
                        ? route(
                            'properties.index',
                            ['giata_id' => optional($record->mapperOracleGiata->first())->giata_id]
                        )
                        : null)
                    ->toggleable(),
                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                //                TextColumn::make('room_classes')
                //                    ->label('Room Classes')
                //                    ->wrap()
                //                    ->toggleable(),
                //                TextColumn::make('rooms')
                //                    ->label('Rooms')
                //                    ->wrap()
                //                    ->toggleable(),
                //                TextColumn::make('room_types')
                //                    ->label('Room Types')
                //                    ->wrap()
                //                    ->toggleable(),

                TextColumn::make('filtered_rooms')
                    ->label('Rooms')
                    ->formatStateUsing(fn ($state) => collect($state)->pluck('roomId')->join(', '))
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View')
                        ->modalWidth('7xl')
                        ->icon('heroicon-o-eye')
                        ->modalHeading('Property Details')
                        ->modalDescription(fn ($record) => $record->name)
                        ->modalContent(fn ($record) => view('livewire.modal.property-view', ['record' => $record])),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.oracle-content-table');
    }
}
