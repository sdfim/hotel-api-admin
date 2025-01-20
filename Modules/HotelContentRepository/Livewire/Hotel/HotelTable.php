<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Enums\RoleSlug;
use App\Models\Property;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\API\Services\MappingCacheService;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Enums\MealPlansEnum;

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
                    ->wrap()
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
                            if ($record->product) $record->product->delete();
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
                    ->createAnother(false)
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
        $hashMapperExpedia =  resolve(MappingCacheService::class)->getMappingsExpediaHashMap();
        $reversedHashMap = array_flip($hashMapperExpedia);
        $expediaCode = $reversedHashMap[$property->code] ?? null;
        $roomsData = [];
        $numRooms = 0;
        $mealPlansRes = [MealPlansEnum::NO_MEAL_PLAN->value];
        if ($expediaCode) {
            $rooms = DB::connection('mysql_cache')
                ->table('expedia_content_slave')
                ->select('rooms', 'statistics',  'all_inclusive')
                ->where('expedia_property_id', $expediaCode)
                ->get();
            if ($rooms->isEmpty()) return;
            $roomsData = json_decode(Arr::get(json_decode($rooms, true)[0], 'rooms', '[]'), true);
            $statistics = json_decode(Arr::get(json_decode($rooms, true)[0], 'statistics', '{}'), true);
            $numRooms = Arr::get($statistics, '52.value', 0);
            $allInclusive = json_decode(Arr::get(json_decode($rooms, true)[0], 'all_inclusive', '{}'), true);
            $mealPlans = MealPlansEnum::values();
            $mealPlansRes = array_filter($allInclusive, function ($value) use ($mealPlans) {
                return in_array($value, $mealPlans);
            });
            $mealPlansRes = array_values($mealPlansRes);
            if (empty($mealPlansRes)) {
                $mealPlansRes = [MealPlansEnum::NO_MEAL_PLAN->value];
            }
        }
        $hotel = \DB::transaction(function () use ($property, $vendorId, $source_id, $roomsData, $numRooms, $mealPlansRes) {
            $hotel = Hotel::create([
                'giata_code' => $property->code,
                'star_rating' => ($property->rating ?? 1) > 0 ? $property->rating : 1,
                'sale_type' => 'Direct Connection',
                'num_rooms' => $numRooms,
                'hotel_board_basis' => $mealPlansRes,
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

            if (!empty($roomsData)) {
                foreach ($roomsData as $room) {
                    $description = Arr::get($room, 'descriptions.overview');
                    $descriptionAfterLayout = preg_replace('/^<p>.*?<\/p>\s*<p>.*?<\/p>\s*/', '', $description);
                    $hotelRoom = $hotel->rooms()->create([
                        'name' => Arr::get($room,'name'),
                        'description' => $descriptionAfterLayout,
                        'supplier_codes' => json_encode([['code' => Arr::get($room,'id'), 'supplier' => 'Expedia']]),
                        'area' => Arr::get($room,'area.square_feet', 0),
                        'room_views' => array_values(array_map(function ($view) {
                            return $view['name'];
                        }, Arr::get($room, 'views', []))),
                        'bed_groups' => array_merge(...array_map(function ($group) {
                            return array_map(function ($config) {
                                return $config['quantity'] . ' ' . $config['size'] . ' Beds';
                            }, $group['configuration']);
                        }, Arr::get($room, 'bed_groups', []))),
                        ]);
                    $attributeIds = [];
                    foreach ($room['amenities'] as $k => $amenity) {
                        // Check if the attribute already exists
                        $attribute = ConfigAttribute::firstOrCreate([
                            'name' => Arr::get($amenity, 'name'),
                            'default_value' => '',
                        ]);
                        // Collect the attribute ID
                        $attributeIds[] = $attribute->id;

                        if ($k > 10) break;
                    }
                    // Attach the attribute IDs to the room
                    $hotelRoom->attributes()->sync($attributeIds);
                }
            }

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
