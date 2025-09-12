<?php

namespace App\Livewire;

use App\Models\HiltonProperty;
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

class HiltonPropertyTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(HiltonProperty::query())
            ->columns([
                TextColumn::make('first_mapperHiltonGiata_code')
                    ->label('Giata Code')
                    ->getStateUsing(fn ($record) => optional($record->mapperHiltonGiata->first())->giata_id)
                    ->url(fn ($record) => $record->mapperHiltonGiata->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperHiltonGiata->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('prop_code')->label('Property Code')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('name')
                    ->wrap()
                    ->label('Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('facility_chain_name')
                    ->wrap()
                    ->label('Chain Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('city')
                    ->label('City')
                    ->wrap()
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('country_code')->label('Country')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('address')->wrap()->label('Address')->toggleable()->sortable()->searchable(isIndividual: true),
                TextColumn::make('postal_code')->label('Postal Code')->toggleable()->sortable()->searchable(isIndividual: true),
                TextColumn::make('latitude')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('longitude')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('phone_number')->label('Phone')->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('email')->label('Email')->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('website')->label('Website')->toggleable()->searchable(isIndividual: true),
                TextColumn::make('star_rating')->label('Stars')->sortable()->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('market_tier')->label('Market Tier')->sortable()->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('year_built')->label('Year Built')->sortable()->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('opening_date')->label('Opening Date')->sortable()->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('time_zone')->label('Time Zone')->toggleable()->searchable(isIndividual: true),
//                TextColumn::make('checkin_time')->label('Check-in')->toggleable()->sortable()->searchable(isIndividual: true),
//                TextColumn::make('checkout_time')->label('Check-out')->toggleable()->sortable()->searchable(isIndividual: true),
//                TextColumn::make('allow_adults_only')->label('Adults Only')->toggleable()->sortable()->searchable(isIndividual: true),
//                TextColumn::make('policy')
//                    ->label('Policy')
//                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
//                    ->wrap()
//                    ->html()
//                    ->toggleable()
//                    ->searchable(isIndividual: true),
                IconColumn::make('has_props')
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
        return view('livewire.hilton-property-table');
    }
}
