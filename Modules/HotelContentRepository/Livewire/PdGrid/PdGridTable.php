<?php

namespace Modules\HotelContentRepository\Livewire\PdGrid;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Livewire\Features\SupportRedirects\Redirector;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\Hotel\HotelForm;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class PdGridTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use PdGridTrait;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(
                Hotel::query()
                    ->when(
                        auth()->user()->currentTeam && !auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->whereHas('product', function (Builder $query) {
                            $query->where('vendor_id', auth()->user()->currentTeam->vendor_id);
                        }),
                    )
            )
            ->columns([
                Tables\Columns\IconColumn::make('product.verified')->label('Verified')->sortable()->boolean(),
                TextColumn::make('product.name')->label('Name')->wrap()->sortable()->searchable()
                    ->url(fn($record) => route('hotel-repository.edit', ['hotel_repository' => $record->id]))
                    ->extraHeaderAttributes([
                        'style' => 'position: sticky; left: 0; z-index: 1; min-width: 200px;',
                        'class' => 'bg-gray-50 dark:bg-gray-900',
                    ])
                    ->extraCellAttributes([
                        'style' => 'position: sticky; left: 0; z-index: 1; min-width: 200px;',
                        'class' => 'bg-white dark:bg-gray-900',
                    ]),

                TextColumn::make('product.vendor.name')->label('Vendor Name')
                    ->wrap()->sortable()
                    ->searchable()
                    ->url(fn($record) => route('vendor-repository.edit', ['vendor_repository' => $record->product->vendor->id]))
                    ->extraCellAttributes([
                        'style' => 'min-width: 200px;',
                    ]),
//                Tables\Columns\IconColumn::make('product.vendor.verified')->label('Vendor Verified')->sortable()->boolean(),
//                TextColumn::make('product.vendor.address')->label('Vendor Address')
//                    ->wrap()->sortable()->searchable()
//                    ->extraCellAttributes([
//                        'style' => 'min-width: 200px;',
//                    ]),
//                TextColumn::make('product.vendor.lat')->label('Vendor Latitude')->sortable()->searchable(),
//                TextColumn::make('product.vendor.lng')->label('Vendor Longitude')->sortable()->searchable(),
//                TextColumn::make('product.vendor.website')->label('Vendor Website')->wrap()->sortable()->searchable(),

                TextColumn::make('product.product_type')->label('Product Type')->sortable()->searchable(),
                TextColumn::make('product.content_source_id')
                    ->label('Content Source')
                    ->sortable()->searchable()
                    ->getStateUsing(fn($record) => $record->product?->contentSource->name),
                TextColumn::make('room_images_source_id')
                    ->label('Room Images Source')
                    ->sortable()->searchable()
                    ->getStateUsing(fn($record) => $record->roomImagesSource->name),
                TextColumn::make('product.property_images_source_id')
                    ->label('Property Images Source')
                    ->sortable()->searchable()
                    ->getStateUsing(fn($record) => $record->product?->propertyImagesSource->name),
                TextColumn::make('product.default_currency')->label('Currency')->sortable()->searchable(),
                TextColumn::make('product.website')->label('Website')->sortable()->searchable(),
//                TextColumn::make('product.location')->label('Location')->sortable()->searchable(),
                TextColumn::make('product.lat')->label('Latitude')->sortable()->searchable(),
                TextColumn::make('product.lng')->label('Longitude')->sortable()->searchable(),

                TextColumn::make('star_rating')->sortable()->searchable(),
                TextColumn::make('num_rooms')->sortable()->searchable(),
                TextColumn::make('weight')->sortable()->searchable(),
                TextColumn::make('sale_type')->sortable()->searchable(),
                TextColumn::make('address')
                    ->wrap()->sortable()->searchable()
                    ->extraCellAttributes([
                        'style' => 'min-width: 200px;',
                    ]),

//                TextColumn::make('hotel_board_basis')->label('Hotel Board Basis')->sortable()->searchable(),
//                TextColumn::make('travel_agent_commission')->label('Travel Agent Commission')->sortable(),

                TextColumn::make('Tax')
                    ->label('Tax')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getFormattedFeeTaxes($record, 'Tax'))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getFormattedFeeTaxes($record, 'Tax'),
                        'style' => 'min-width: 350px;',
                        ]),
                TextColumn::make('Additional-Fees')
                    ->label('Additional Fees')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getFormattedFeeTaxes($record, 'Fee'))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getFormattedFeeTaxes($record, 'Fee'),
                        'style' => 'min-width: 350px;',
                        ]),

                TextColumn::make('Property-Notes')
                    ->label('Property Notes')
                    ->wrap()->limit(200)
                    ->getStateUsing(fn($record) => $this->getPropertyNotes($record))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getFormattedFeeTaxes($record, 'Fee'),
                        'style' => 'min-width: 250px;',
                    ]),

                TextColumn::make('Breakfast-Included-for-all-guests')
                    ->label('Breakfast Included for all guests')
                    ->wrap()->limit(50)
                    ->getStateUsing(fn($record) => $this->getMealPlansAvailable($record, 'Breakfast Included for all guests'))
                    ->extraCellAttributes(fn($record) => ['style' => 'min-width: 100px;'])
                    ->alignment(Alignment::Center),

                TextColumn::make('Virtuoso')
                    ->label('Virtuoso')
                    ->wrap()->limit(50)
                    ->getStateUsing(fn($record) => $this->getConsortiaExit($record, 'Virtuoso'))
                    ->extraCellAttributes(fn($record) => ['style' => 'min-width: 100px;'])
                    ->alignment(Alignment::Center),
                TextColumn::make('Virtuoso Amenities')
                    ->label('Virtuoso Amenities')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getConsortia($record, 'Virtuoso'))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getConsortia($record, 'Virtuoso'),
                        'style' => 'min-width: 350px;',
                        ]),
//                TextColumn::make('Virtuoso Ultimate Amenities: Terms and Conditions')
//                    ->label('Ultimate Amenities: Terms and Conditions')
//                    ->getStateUsing(fn() => ''),
//                TextColumn::make('Virtuoso Ultimate Amenities: Inclusions')
//                    ->label('Ultimate Amenities: Inclusions')
//                    ->getStateUsing(fn() => ''),

                TextColumn::make('Signature')
                    ->label('Signature')
                    ->wrap()->limit(50)
                    ->getStateUsing(fn($record) => $this->getConsortiaExit($record, 'Signature'))
                    ->extraCellAttributes(fn($record) => ['style' => 'min-width: 100px;'])
                    ->alignment(Alignment::Center),
                TextColumn::make('Signature Amenities')
                    ->label('Signature Amenities')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getConsortia($record, 'Signature'))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getConsortia($record, 'Signature'),
                        'style' => 'min-width: 350px;',
                        ]),
//                TextColumn::make('Signature Ultimate Amenities: Terms and Conditions')
//                    ->label('Ultimate Amenities: Terms and Conditions')
//                    ->getStateUsing(fn() => ''),
//                TextColumn::make('Signature Ultimate Amenities: Inclusions')
//                    ->label('Ultimate Amenities: Inclusions')
//                    ->getStateUsing(fn() => ''),

                TextColumn::make('Travel Leaders')
                    ->label('Travel Leaders')
                    ->wrap()->limit(50)
                    ->getStateUsing(fn($record) => $this->getConsortiaExit($record, 'Travel Leaders'))
                    ->extraCellAttributes(fn($record) => ['style' => 'min-width: 100px;'])
                    ->alignment(Alignment::Center),
                TextColumn::make('Travel Leaders Amenities')
                    ->label('Travel Leaders Amenities')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getConsortia($record, 'Travel Leaders'))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getConsortia($record, 'Travel Leaders'),
                        'style' => 'min-width: 350px;',
                        ]),
//                TextColumn::make('Travel Leaders Ultimate Amenities: Terms and Conditions')
//                    ->label('Ultimate Amenities: Terms and Conditions')
//                    ->getStateUsing(fn() => ''),
//                TextColumn::make('Travel Leaders Ultimate Amenities: Inclusions')
//                    ->label('Ultimate Amenities: Inclusions')
//                    ->getStateUsing(fn() => ''),


                TextColumn::make('Direct Connect')
                    ->label('Direct Connect')
                    ->wrap()->limit(50)
                    ->getStateUsing(fn($record) => $record->sale_type),

                TextColumn::make('Payment-Terms')
                    ->label('Payment Terms')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getDepositInformation($record))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getDepositInformation($record),
                        'style' => 'min-width: 350px;',
                        ]),

                TextColumn::make('Cancellation-Policy')
                    ->label('Cancellation Policy')
                    ->wrap()->limit(300)
                    ->getStateUsing(fn($record) => $this->getCancellationPolicy($record))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getCancellationPolicy($record),
                        'style' => 'min-width: 350px;',
                        ]),

                TextColumn::make('Inclusions')
                    ->label('Inclusions')
                    ->wrap()->limit(100)
                    ->getStateUsing(fn($record) => $this->getInclusions($record, 'Inclusions'))
                    ->extraCellAttributes(fn($record) => [
                        'title' => $this->getInclusions($record, 'Inclusions'),
                        'style' => 'min-width: 150px;',
                        ]),

                TextColumn::make('All-Inclusive')
                    ->label('All Inclusive')
                    ->wrap()->limit(150)
                    ->getStateUsing(fn($record) => $this->getMealPlansAvailable($record, 'All Inclusive'))
                    ->extraCellAttributes(fn($record) => ['style' => 'min-width: 150px;'])
                    ->alignment(Alignment::Center),

                TextColumn::make('PD Contact E-Mail')
                    ->label('PD Contact E-Mail')
                    ->wrap()->limit(100)
                    ->getStateUsing(fn($record) => $this->getContactInformationEmail($record, 'PD Contact'))
                    ->extraCellAttributes(fn($record) => ['title' => $this->getContactInformationEmail($record, 'PD Contact')]),

                TextColumn::make('Res Email')
                    ->label('Res Email')
                    ->wrap()->limit(100)
                    ->getStateUsing(fn($record) => $this->getContactInformationEmail($record, 'Reservation'))
                    ->extraCellAttributes(fn($record) => ['title' => $this->getContactInformationEmail($record, 'Reservation')]),

                TextColumn::make('Sales Email')
                    ->label('Sales Email')
                    ->wrap()->limit(100)
                    ->getStateUsing(fn($record) => $this->getContactInformationEmail($record, 'Sales Marketing'))
                    ->extraCellAttributes(fn($record) => ['title' => $this->getContactInformationEmail($record, 'Sales Marketing')]),

                TextColumn::make('Concierge Email')
                    ->label('Concierge Email')
                    ->wrap()->limit(100)
                    ->getStateUsing(fn($record) => $this->getContactInformationEmail($record, 'Concierge'))
                    ->extraCellAttributes(fn($record) => ['title' => $this->getContactInformationEmail($record, 'Concierge')]),

            ]);
    }

    public function render(): View
    {
        return view('livewire.products.pd-grid-table');
    }
}
