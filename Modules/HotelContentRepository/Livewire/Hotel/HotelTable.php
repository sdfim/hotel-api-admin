<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use App\Helpers\Strings;
use App\Models\Property;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\Vendor;

class HotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Vendor $vendor = null;

    public function mount(?Vendor $vendor = null)
    {
        $this->vendor = $vendor;
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function () {
                $query = Hotel::query()
                    ->when(
                        auth()->user()->currentTeam && !auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->whereHas('product', function (Builder $query) {
                            $query->where('vendor_id', auth()->user()->currentTeam->vendor_id);
                        }),
                    );
                if ($this->vendor?->id) {
                    $query->whereHas('product', function ($query) {
                        $query->where('vendor_id', $this->vendor->id);
                    });
                }
                return $query;
            })
            ->columns([
                ImageColumn::make('product.hero_image_thumbnails')
                    ->size('100px'),

                IconColumn::make('product.verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('product.name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('giata_code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('product.address')
                    ->label('Address')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $string = '';
                        foreach ($record->address as $item) {
                            if (is_array($item)) continue;
                            $string .= $item . ', ';
                        }
                        return rtrim($string, ', ');
                    })
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('star_rating')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('num_rooms')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('combined_sources')
                    ->label('Combined Sources')
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return 'Content: ' . $record->product?->contentSource->name . '<br>'
                            . 'Room Images: ' . $record->roomImagesSource->name . '<br>'
                            . 'Property Images: ' . $record->product?->propertyImagesSource->name;
                    })
                    ->html(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->date()
                    ->sortable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn (Hotel $record): string => route('hotel-repository.edit', $record))
                    ->visible(fn (Hotel $record) => Gate::allows('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->visible(fn (Hotel $record): bool => Gate::allows('delete', $record))
                    ->action(function (Hotel $record) {
                        \DB::transaction(function () use ($record) {
                            $record->product->delete();
                            $record->delete();
                        });
                        Notification::make()
                            ->title('Hotel deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make()
//                    ->form((new HotelForm())->schemeForm())
//                    ->visible(Gate::allows('create', Hotel::class))
//                    ->tooltip('Add New Hotel')
//                    ->icon('heroicon-o-plus')
//                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
//                    ->iconButton()
//                    ->url(route('hotel-repository.create')),
                Tables\Actions\CreateAction::make('addHotelWithGiataCode')
                    ->label('Add Hotel with GIATA Code')
                    ->icon('heroicon-o-document-plus')
                    ->iconButton()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(function (array $data) {
                        $this->saveHotelWithGiataCode($data);
                    })
                    ->modalHeading('Add Hotel with GIATA Code')
                    ->modalWidth('lg')
                    ->form(HotelForm::getCoreFields()),
            ]);
    }

    public function saveHotelWithGiataCode($data): void
    {
        $property = Property::find($data['giata_code']);
        $vendorId = $data['product']['vendor_id'];
        $source_id = ContentSource::where('name', ContentSourceEnum::EXPEDIA->value)->first()->id ?? 1;

        if (!$property) {
            Notification::make()
                ->title('Property not found')
                ->error()
                ->send();
            return;
        }

        $hotel = \DB::transaction(function () use ($property, $vendorId, $source_id) {
            $hotel = Hotel::create([
                'giata_code' => $property->code,
                'star_rating' => $property->rating,
                'sale_type' => 'Direct connection',
                'num_rooms' => 0,
                'room_images_source_id' => $source_id,
                'address' => [
                    'line_1' => $property->mapper_address ?? '',
                    'city' => $property->city ?? '',
                    'country_code' => $property->address->CountryName ?? '',
                    'state_province_name' => $property->address->AddressLine ?? '',
                ]
            ]);

            $hotel->product()->create([
                'name' => $property->name,
                'vendor_id' => $vendorId,
                'product_type' => 'hotel',
                'default_currency' => 'USD',
                'verified' => false,
                'content_source_id' => $source_id,
                'property_images_source_id' => $source_id,
                'lat' => $property->latitude,
                'lng' => $property->longitude,
            ]);

            return $hotel;
        });

        Notification::make()
            ->title('Hotel created successfully')
            ->success()
            ->send();

        if ($hotel) {
            $this->redirect(route('hotel-repository.edit', $hotel));
        } else {
            $this->redirect(route('hotel-repository.index'));
        }
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-table');
    }
}
