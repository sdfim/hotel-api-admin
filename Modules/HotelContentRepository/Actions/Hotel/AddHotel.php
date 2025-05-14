<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use App\Models\ExpediaContent;
use App\Models\ExpediaContentSlave;
use App\Models\Property;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\API\Services\MappingCacheService;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\MealPlansEnum;
use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Events\Hotel\HotelAdded;
use Modules\HotelContentRepository\Livewire\Hotel\HotelForm;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductAttribute;

class AddHotel
{
    public function handle(HotelRequest $request)
    {
        $hotel = Hotel::create($request->validated());
        HotelAdded::dispatch($hotel);

        return $hotel;
    }

    public function saveWithGiataCode(array $data): Hotel
    {
        $property = Property::find($data['giata_code']);
        $vendorId = $data['product']['vendor_id'];
        $source_id = ContentSource::where('name', ContentSourceEnum::EXPEDIA->value)->first()->id ?? 1;

        if (! $property) {
            throw new \Exception('Property not found');
        }

        /** @var MappingCacheService $mappingCacheService */
        $mappingCacheService = app(MappingCacheService::class);
        $hashMapperExpedia = $mappingCacheService->getMappingsExpediaHashMap();
        $reversedHashMap = array_flip($hashMapperExpedia);
        $expediaCode = $reversedHashMap[$property->code] ?? null;

        $roomsData = [];
        $attributes = [];
        $roomsOccupancy = [];
        $numRooms = 0;
        $mealPlansRes = [MealPlansEnum::NO_MEAL_PLAN->value];
        $ratingExpedia = 0;

        if (! $expediaCode) {
            Notification::make()
                ->title('Expedia hotel not found in the mapper.')
                ->danger()
                ->send();
        }

        if ($expediaCode) {
            $expediaData = ExpediaContentSlave::select('rooms', 'statistics', 'all_inclusive', 'amenities', 'attributes', 'themes', 'rooms_occupancy')
                ->where('expedia_property_id', $expediaCode)
                ->first();
            $expediaData = $expediaData ? $expediaData->toArray() : [];

            $expediaMainData = ExpediaContent::select('rating')
                ->where('property_id', $expediaCode)
                ->first();
            $expediaMainData = $expediaMainData ? $expediaMainData->toArray() : [];

            if (empty($expediaData) && ! empty($expediaMainData)) {
                Notification::make()
                    ->title('Hotel Expedia extended content data is not available on Stage.')
                    ->danger()
                    ->send();
            }
            if (empty($expediaData) && empty($expediaMainData)) {
                Notification::make()
                    ->title('Expedia hotel not found in the mapper.')
                    ->danger()
                    ->send();
            }

            if (! empty($expediaMainData)) {
                $ratingExpedia = Arr::get($expediaMainData, 'rating', 0);
            }

            if (! empty($expediaData)) {
                $roomsData = Arr::get($expediaData, 'rooms', []);
                $statistics = Arr::get($expediaData, 'statistics', []);
                $roomsOccupancy = Arr::get($expediaData, 'rooms_occupancy', []);

                $attributesP1 = Arr::get($expediaData, 'amenities', []);
                $attributesP2 = Arr::get(Arr::get($expediaData, 'attributes', []), 'general', []);
                $attributesP3 = Arr::get($expediaData, 'themes', []);
                $attributes = array_merge($attributesP1, $attributesP2, $attributesP3);
                $attributes = collect($attributes)
                    ->filter(function ($value) {
                        return is_array($value) && ! empty($value['name']) && ! str_contains($value['name'], 'COVID-19');
                    })
                    ->flatMap(function ($value) {
                        $result = [$value];
                        if (str_contains($value['name'], 'Family')) {
                            $newValue['name'] = 'Family Friendly';
                            $result[] = $newValue;
                        }

                        return $result;
                    })
                    ->values()
                    ->all();

                $numRooms = Arr::get($statistics, '52.value', 0);
                $allInclusive = Arr::get($expediaData, 'all_inclusive', []);
                $mealPlans = MealPlansEnum::values();
                $mealPlansRes = array_filter($allInclusive, fn ($value) => in_array($value, $mealPlans));
                $mealPlansRes = array_values($mealPlansRes) ?: [MealPlansEnum::NO_MEAL_PLAN->value];
            } else {
                Notification::make()
                    ->title('Rooms not found')
                    ->danger()
                    ->send();
            }
        }

        /** @var HotelForm $hotelForm */
        $hotelForm = app(HotelForm::class);
        $address = $property->latitude && $property->longitude
            ? $hotelForm->getGeocodingData($property->latitude, $property->longitude)
            : [];

        return DB::transaction(function () use (
            $property, $roomsData, $vendorId, $source_id, $numRooms, $mealPlansRes, $attributes, $address, $roomsOccupancy, $ratingExpedia) {
            $hotel = Hotel::updateOrCreate(
                ['giata_code' => $property->code],
                [
                    'star_rating' => max($property->rating ?? 1, 1, $ratingExpedia),
                    'sale_type' => 'Direct Connection',
                    'num_rooms' => $numRooms,
                    'hotel_board_basis' => $mealPlansRes,
                    'room_images_source_id' => $source_id,
                    'address' => [
                        'line_1' => Arr::get($address, 'line_1', null) ?? $property->mapper_address ?? '',
                        'city' => Arr::get($address, 'city', null) ?? $property->city ?? '',
                        'country_code' => Arr::get($address, 'country_code', null) ?? $property->address->CountryName ?? '',
                        'state_province_name' => Arr::get($address, 'state_province_name', null) ?? $property->address->AddressLine ?? '',
                    ],
                ]
            );

            $product = $hotel->product()->updateOrCreate(
                ['vendor_id' => $vendorId],
                [
                    'name' => $property->name,
                    'product_type' => 'hotel',
                    'default_currency' => 'USD',
                    'verified' => false,
                    'content_source_id' => $source_id,
                    'property_images_source_id' => $source_id,
                    'lat' => $property->latitude,
                    'lng' => $property->longitude,
                ]
            );

            if (! empty($roomsData)) {
                foreach ($roomsData as $room) {
                    $roomId = Arr::get($room, 'id', 0);
                    $description = Arr::get($room, 'descriptions.overview');
                    $descriptionAfterLayout = preg_replace('/^<p>.*?<\/p>\s*<p>.*?<\/p>\s*/', '', $description);
                    $maxRoomOccupancy = Arr::get($roomsOccupancy, $roomId.'.occupancy.max_allowed.total', 0);
                    $hotelRoom = $hotel->rooms()->updateOrCreate(
                        ['name' => Arr::get($room, 'name')],
                        [
                            'description' => $descriptionAfterLayout,
                            'supplier_codes' => json_encode([['code' => Arr::get($room, 'id'), 'supplier' => 'Expedia']]),
                            'area' => Arr::get($room, 'area.square_feet', 0),
                            'room_views' => array_values(array_map(function ($view) {
                                return $view['name'];
                            }, Arr::get($room, 'views', []))),
                            'bed_groups' => array_merge(...array_map(function ($group) {
                                return array_map(function ($config) {
                                    return $config['quantity'].' '.$config['size'].' Beds';
                                }, $group['configuration']);
                            }, Arr::get($room, 'bed_groups', []))),
                            'max_occupancy' => $maxRoomOccupancy,
                        ]);
                    $attributeIds = [];
                    $amenities = Arr::get($room, 'amenities', []);
                    foreach ($amenities as $k => $amenity) {
                        if (! is_array($amenity)) {
                            continue;
                        }
                        $amenityName = Arr::get($amenity, 'name' ?? '');
                        // Check if the attribute already exists
                        $attribute = ConfigAttribute::firstOrCreate([
                            'name' => $amenityName,
                            'default_value' => $amenityName.' room',
                        ]);
                        // Collect the attribute ID
                        $attributeIds[] = $attribute->id;
                    }
                    // Attach the attribute IDs to the room
                    $hotelRoom->attributes()->sync($attributeIds);
                }
            }

            // Check and add amenities to ConfigAttribute and attach to ProductAttribute
            $attributesData = [];
            foreach ($attributes as $attribute) {
                $attributeName = Arr::get($attribute, 'name', '');
                $attributeCategory = Arr::get($attribute, 'categories.0', 'general');
                $category = ConfigAttributeCategory::firstOrCreate([
                    'name' => $attributeCategory,
                ]);
                $attribute = ConfigAttribute::firstOrCreate([
                    'name' => $attributeName,
                    'default_value' => $attributeName.' hotel',
                ]);

                $attribute->categories()->syncWithoutDetaching([$category->id]);

                $attributesData[] = [
                    'product_id' => $product->id,
                    'config_attribute_id' => $attribute->id,
                ];
            }

            $existingAttributes = ProductAttribute::whereIn('product_id', array_column($attributesData, 'product_id'))
                ->whereIn('config_attribute_id', array_column($attributesData, 'config_attribute_id'))
                ->get(['product_id', 'config_attribute_id'])
                ->toArray();

            $filteredAttributesData = array_filter($attributesData, function ($item) use ($existingAttributes) {
                foreach ($existingAttributes as $existing) {
                    if (
                        $existing['product_id'] === $item['product_id'] &&
                        $existing['config_attribute_id'] === $item['config_attribute_id']
                    ) {
                        return false;
                    }
                }

                return true;
            });

            ProductAttribute::upsert($filteredAttributesData, ['product_id', 'config_attribute_id']);

            return $hotel;
        });
    }

    public function create(array $data): Hotel
    {
        $data['address'] = $data['addressArr'];

        $hotel = Hotel::create(Arr::only($data, [
            'weight',
            'giata_code',
            'featured_flag',
            'sale_type',
            'address',
            'star_rating',
            'num_rooms',
            'room_images_source_id',
            'hotel_board_basis',
            'travel_agent_commission',
        ]));

        $data['product']['product_type'] = 'hotel';
        $data['product']['verified'] = false;
        $data['product']['onSale'] = false;

        $product = $hotel->product()->create(Arr::only($data['product'], [
            'vendor_id',
            'hero_image',
            'hero_image_thumbnails',
            'product_type',
            'name',
            'verified',
            'onSale',
            'lat',
            'lng',
            'content_source_id',
            'property_images_source_id',
            'default_currency',
            'website',
            'off_sale_by_sources',
        ]));

        if (isset($data['galleries'])) {
            $hotel->product->galleries()->sync($data['galleries']);
        }

        if (isset($data['channels'])) {
            $hotel->product->channels()->sync($data['channels']);
        }

        return $hotel;
    }

    public function update(Hotel $hotel, array $data): Hotel
    {
        if (! isset($data['product']['verified'])) {
            $data['verified'] = false;
        }
        if (! isset($data['product']['onSale'])) {
            $data['onSale'] = false;
        }

        $data['product']['off_sale_by_sources'] = array_keys(array_filter($data['off_save'], function ($value) {
            return $value === true;
        }));

        $data['address'] = $data['addressArr'];

        $productData = Arr::only($data['product'], [
            'vendor_id',
            'name',
            'verified',
            'onSale',
            'hero_image',
            'hero_image_thumbnails',
            'lat',
            'lng',
            'content_source_id',
            'property_images_source_id',
            'default_currency',
            'website',
            'off_sale_by_sources',
        ]);

        $hotel->product->update($productData);

        $hotel->update(Arr::only($data, [
            'weight',
            'is_not_auto_weight',
            'giata_code',
            'featured_flag',
            'sale_type',
            'address',
            'star_rating',
            'num_rooms',
            'room_images_source_id',
            'hotel_board_basis',
            'travel_agent_commission',
        ]));

        if (isset($data['galleries'])) {
            $hotel->product->galleries()->sync($data['galleries']);
        }

        if (isset($data['channels'])) {
            $hotel->product->channels()->sync($data['channels']);
        }

        if ($hotel->product->vendor->independent_flag) {
            $vendor = $hotel->product->vendor;
            $vendor->lat = $data['product']['lat'];
            $vendor->name = $data['product']['name'];
            $vendor->lng = $data['product']['lng'];
            $vendor->website = $data['product']['website'];
            $vendor->address = $data['addressArr'];
            $vendor->save();
        }

        return $hotel;
    }

    public function getMaxOccupancy(array $data): int
    {
        $maxOccupancy = 0;

        foreach ($data as $item) {
            $currentOccupancy = $item['occupancy']['max_allowed']['total'] ?? 0;
            if ($currentOccupancy > $maxOccupancy) {
                $maxOccupancy = $currentOccupancy;
            }
        }

        return $maxOccupancy;
    }
}
