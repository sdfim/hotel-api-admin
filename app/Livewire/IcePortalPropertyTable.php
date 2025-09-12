<?php

namespace App\Livewire;

use App\Models\IcePortalPropertyAsset;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class IcePortalPropertyTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(IcePortalPropertyAsset::query())
            ->columns([
                TextColumn::make('first_mapperHbsiGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperHbsiGiata->first())
                        ->giata_id)
                    ->url(fn ($record) => $record->mapperHbsiGiata->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperHbsiGiata->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('listingID')
                    ->label('Listing ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->wrap()
                    ->html()
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('addressLine1')
                    ->wrap()
                    ->label('Address')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('country')
                    ->wrap()
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('postalCode')
                    ->toggleable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('latitude')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(),
                IconColumn::make('has_room_types')
                    ->label('Room Types')
                    ->boolean(),
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
        return view('livewire.ice-portal-property-table');
    }
}
