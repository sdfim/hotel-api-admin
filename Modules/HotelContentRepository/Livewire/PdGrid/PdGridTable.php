<?php

namespace Modules\HotelContentRepository\Livewire\PdGrid;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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

    public function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50])
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
                TextColumn::make('product.name')->label('Name')->sortable(),

                TextColumn::make('product.vendor.name')->label('Vendor Name')->sortable(),
                TextColumn::make('product.vendor.verified')->label('Vendor Verified')->sortable(),
                TextColumn::make('product.vendor.address')->label('Vendor Address')->sortable(),
                TextColumn::make('product.vendor.lat')->label('Vendor Latitude')->sortable(),
                TextColumn::make('product.vendor.lng')->label('Vendor Longitude')->sortable(),
                TextColumn::make('product.vendor.website')->label('Vendor Website')->sortable(),

                TextColumn::make('product.product_type')->label('Product Type')->sortable(),
                TextColumn::make('product.content_source_id')
                    ->label('Content Source')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->product?->contentSource->name),
                TextColumn::make('room_images_source_id')
                    ->label('Room Images Source')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->roomImagesSource->name),
                TextColumn::make('product.property_images_source_id')
                    ->label('Property Images Source')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->product?->propertyImagesSource->name),
                TextColumn::make('product.default_currency')->label('Default Currency')->sortable(),
                TextColumn::make('product.website')->label('Website')->sortable(),
                TextColumn::make('product.location')->label('Location')->sortable(),
                TextColumn::make('product.lat')->label('Latitude')->sortable(),
                TextColumn::make('product.lng')->label('Longitude')->sortable(),

                TextColumn::make('star_rating')->sortable(),
                TextColumn::make('num_rooms')->sortable(),
                TextColumn::make('weight')->sortable(),
                TextColumn::make('sale_type')->sortable(),
                TextColumn::make('address')->sortable(),

                TextColumn::make('hotel_board_basis')->label('Hotel Board Basis')->sortable(),
                TextColumn::make('travel_agent_commission')->label('Travel Agent Commission')->sortable(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.products.pd-grid-table');
    }
}
