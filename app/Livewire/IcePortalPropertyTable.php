<?php

namespace App\Livewire;

use App\Models\IcePortalPropertyAsset;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
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
                    ->getStateUsing(fn ($record) => optional($record->mapperHbsiGiata->first())->giata_id)
                    ->url(fn ($record) => $record->mapperHbsiGiata->first()
                        ? route('properties.index', ['giata_id' => optional($record->mapperHbsiGiata->first())->giata_id])
                        : null)
                    ->toggleable(),
                TextColumn::make('listingID')
                    ->sortable()
                    ->toggleable()
                    ->searchable(isIndividual: true),
                TextColumn::make('name')
                    ->wrap()
                    ->html()
                    ->toggleable()
                    ->sortable()
                    ->searchable(isIndividual: true),

                //                TextColumn::make('supplierId')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('supplierChainCode')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('supplierMappedID')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('createdOn')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('propertyLastModified')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('contentLastModified')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('makeLiveDate')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('makeLiveBy')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('editDate')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('editBy')->toggleable()->sortable()->searchable(isIndividual: true),

                TextColumn::make('addressLine1')
                    ->wrap()
                    ->label('Address')
                    ->toggleable()
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('city')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('country')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('postalCode')->toggleable()->sortable()->searchable(isIndividual: true),
                TextColumn::make('latitude')->sortable()->toggleable()->searchable(isIndividual: true),
                TextColumn::make('longitude')->sortable()->toggleable()->searchable(isIndividual: true),
                //                TextColumn::make('listingClassName')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('regionCode')->toggleable()->sortable()->searchable(isIndividual: true),
                TextColumn::make('phone')->toggleable()->searchable(isIndividual: true),

                //                TextColumn::make('publicationStatus')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('publishedDate')->toggleable()->sortable()->searchable(isIndividual: true),

                //                TextColumn::make('roomTypes')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('meetingRooms')->toggleable()->sortable()->searchable(isIndividual: true),

                IconColumn::make('has_room_types')
                    ->label('Room Types')
                    ->boolean(),

                //                TextColumn::make('iceListingQuantityScore')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('iceListingSizeScore')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('iceListingCategoryScore')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('iceListingRoomScore')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('iceListingScore')->toggleable()->sortable()->searchable(isIndividual: true),
                //                TextColumn::make('bookingListingScore')->toggleable()->sortable()->searchable(isIndividual: true),

                TextColumn::make('listingURL')->toggleable()->sortable()->searchable(isIndividual: true),
            ]);
    }

    public function render(): View
    {
        return view('livewire.ice-portal-property-table');
    }
}
