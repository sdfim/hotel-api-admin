<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use App\Jobs\GetHBSIDataJob;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use App\Models\ExpediaContent;
use App\Models\ExpediaContentSlave;
use App\Models\HotelTraderProperty;
use App\Models\Mapping;
use App\Models\Property;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\API\Services\MappingCacheService;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\MealPlansEnum;
use Modules\Enums\SupplierNameEnum;
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
        $source_id = ContentSource::where('name', ContentSourceEnum::ICE_PORTAL->value)->first()->id ?? 1;

        if (! $property) {
            throw new \Exception('Property not found');
        }

        $dataSupplier['roomsData'] = [];
        $dataSupplier['attributes'] = [];
        $dataSupplier['roomsOccupancy'] = [];
        $dataSupplier['numRooms'] = 0;
        $dataSupplier['mealPlansRes'] = [MealPlansEnum::NO_MEAL_PLAN->value];
        $dataSupplier['ratingSupplier'] = 0;

        if ($data['main_supplier'] === SupplierNameEnum::EXPEDIA->value) {
            $dataSupplier = $this->getExpediaHotelData($property);
        }

        if ($data['main_supplier'] === SupplierNameEnum::HOTEL_TRADER->value) {
            $dataSupplier = $this->getHotelTraderHotelData($property);
        }

        foreach ($data['suppliers'] as $supplier) {
            if ($supplier === SupplierNameEnum::HOTEL_TRADER->value) {
                $dataRoomSupplier[$supplier] = $this->getHotelTraderHotelData($property)['roomsData'] ?? [];
            } elseif ($supplier === SupplierNameEnum::EXPEDIA->value) {
                $dataRoomSupplier[$supplier] = $this->getExpediaHotelData($property)['roomsData'] ?? [];
                foreach ($dataRoomSupplier[$supplier] as &$roomSupplier) {
                    $roomSupplier['supplier'] = SupplierNameEnum::EXPEDIA->value;
                }
            }
        }

        $aiSupplierCodes = [];
        if ($data['auto_marge']) {
            $giataId = $property->code;
            $supplierDataForMerge = [];
            foreach ($dataRoomSupplier as $supplierName => $rooms) {
                foreach ($rooms as $room) {
                    $supplierDataForMerge[$supplierName][] = [
                        'code' => $room['id'] ?? $room['code'] ?? '',
                        'name' => $room['name'] ?? '',
                    ];
                }
            }

            // get HBSI data by request
            Artisan::call('hbsi:get-data', ['giataId' => $giataId]);

            $hbsiDataForMerge = Cache::get('hbsi_supplier_data_'.$giataId, []);
            if (! empty($hbsiDataForMerge)) {
                $supplierDataForMerge[SupplierNameEnum::HBSI->value] = $hbsiDataForMerge;
            }

            $supplierDataJson = json_encode($supplierDataForMerge, JSON_UNESCAPED_UNICODE);
            // Call the merge:suppliers console command
            Artisan::call('merge:suppliers', [
                'supplierData' => $supplierDataJson,
                'giata_id' => $giataId,
            ]);
            // Retrieve merged data from cache
            $cacheKey = 'supplier_merge_data'.($giataId ? "_{$giataId}" : '');
            $mergedDataArray = Cache::get($cacheKey);

            foreach ($mergedDataArray as $mergedRoom) {
                $supplierCodes = [];
                $externalCode = '';
                foreach ($mergedRoom['listings_to_merge'] as $listing) {
                    $supplierCodes[] = [
                        'code' => $listing['code'],
                        'name' => $listing['name'],
                        'supplier' => $listing['supplier'],
                    ];
                    if ($listing['supplier'] === $data['main_supplier']) {
                        $externalCode = $listing['code'];
                    }
                }
                $aiSupplierCodes[$externalCode]['supplier_codes'] = json_encode($supplierCodes, JSON_UNESCAPED_UNICODE);
                $aiSupplierCodes[$externalCode]['external_code'] = 'external_'.$externalCode;
            }
            //            dd($dataSupplier, $mergedDataArray, $data, $aiSupplierCodes);

        } else {
            $dataSupplier['roomsData'] = array_merge(...array_values($dataRoomSupplier));
            foreach ($dataSupplier['roomsData'] as &$room) {
                $room['supplier_codes'] = json_encode([[
                    'code' => $room['id'],
                    'name' => $room['name'] ?? '',
                    'supplier' => $room['supplier'] ?? '',
                ]]);
                $room['external_code'] = $room['id'] ? 'external_'.$room['id'] : '';
            }
        }

        /** @var HotelForm $hotelForm */
        $hotelForm = app(HotelForm::class);
        $address = $property->latitude && $property->longitude
            ? $hotelForm->getGeocodingData($property->latitude, $property->longitude)
            : [];

        return DB::transaction(function () use (
            $property, $vendorId, $source_id, $address, $dataSupplier, $aiSupplierCodes) {
            $hotel = Hotel::updateOrCreate(
                ['giata_code' => $property->code],
                [
                    'star_rating' => max($property->rating ?? 1, 1, $dataSupplier['ratingSupplier']),
                    'sale_type' => 'Direct Connection',
                    'num_rooms' => $dataSupplier['numRooms'],
                    'hotel_board_basis' => $dataSupplier['mealPlansRes'],
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

            if (! empty($dataSupplier['roomsData'])) {
                foreach ($dataSupplier['roomsData'] as $room) {
                    $roomId = Arr::get($room, 'id', 0);
                    $description = Arr::get($room, 'descriptions.overview');
                    $descriptionAfterLayout = preg_replace('/^<p>.*?<\/p>\s*<p>.*?<\/p>\s*/', '', $description);
                    $maxRoomOccupancy = Arr::get($dataSupplier['roomsOccupancy'], $roomId.'.occupancy.max_allowed.total', 0);

                    $roomSupplierCodes = Arr::get($room, 'supplier_codes') ?? json_encode([['code' => Arr::get($room, 'id'), 'supplier' => $room['supplier']]]);
                    $roomSupplierCodes = ! empty($aiSupplierCodes) && isset($aiSupplierCodes[$roomId])
                        ? $aiSupplierCodes[$roomId]['supplier_codes']
                        : $roomSupplierCodes;

                    $hotelRoom = $hotel->rooms()->updateOrCreate(
                        ['name' => Arr::get($room, 'name').' ('.Arr::get($room, 'id').')'],
                        [
                            'description' => $descriptionAfterLayout,
                            'supplier_codes' => $roomSupplierCodes,
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
                            'external_code' => Arr::get($room, 'external_code', 'external_'.$roomId),
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
                    //                    dump($hotelRoom->name);
                }
            }

            // Check and add amenities to ConfigAttribute and attach to ProductAttribute
            $attributesData = [];
            foreach ($dataSupplier['attributes'] as $attribute) {
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

    protected function getHotelTraderHotelData($property): array
    {
        $hotelTraderCode = Mapping::where('giata_id', $property->code)
            ->where('supplier', 'hotelTrader')
            ->first()?->supplier_id;

        $result = [
            'hotelTraderCode' => $hotelTraderCode,
            'roomsData' => [],
            'roomsOccupancy' => [],
            'numRooms' => 0,
            'attributes' => [],
            'mealPlansRes' => [MealPlansEnum::NO_MEAL_PLAN->value],
            'ratingSupplier' => 0,
        ];

        if (! $hotelTraderCode) {
            Notification::make()
                ->title('HotelTrader hotel not found in the mapper.')
                ->danger()
                ->send();

            return $result;
        }

        $hotelTraderData = HotelTraderProperty::where('propertyId', $hotelTraderCode)->first();
        $hotelTraderData = $hotelTraderData ? $hotelTraderData->toArray() : [];

        if (empty($hotelTraderData)) {
            Notification::make()
                ->title('HotelTrader hotel not found in the mapper.')
                ->danger()
                ->send();

            return $result;
        }

        // Transform ratingSupplier
        $result['ratingSupplier'] = (float) ($hotelTraderData['starRating'] ?? 0);

        // Transform roomsData and roomsOccupancy
        $rooms = $hotelTraderData['rooms'] ?? [];

        $result['numRooms'] = count($rooms);
        foreach ($rooms as $room) {
            $roomId = $room['roomCode'] ?? $room['displayName'] ?? null;
            $result['roomsData'][] = [
                'id' => $roomId,
                'name' => $room['displayName'] ?? '',
                'descriptions' => [
                    'overview' => $room['shortDesc'] ?? '',
                ],
                'area' => null, // Not provided
                'views' => [], // Not provided
                'bed_groups' => [], // Not provided
                'amenities' => [], // Not provided
                'supplier' => SupplierNameEnum::HOTEL_TRADER->value,
            ];
            $result['roomsOccupancy'][$roomId] = [
                'occupancy' => [
                    'max_allowed' => [
                        'total' => (int) ($room['totalMaxOccupancy'] ?? 0),
                        'adults' => (int) ($room['maxAdultOccupancy'] ?? 0),
                        'children' => (int) ($room['maxChildOccupancy'] ?? 0),
                    ],
                ],
            ];
        }

        // Transform attributes (if any hotel-level attributes are available)
        $attributes = [];
        if (! empty($hotelTraderData['longDescription'])) {
            $attributes[] = [
                'name' => 'Description',
                'value' => $hotelTraderData['longDescription'],
                'categories' => ['general'],
            ];
        }
        $result['attributes'] = $attributes;

        // Meal plans (default to NO_MEAL_PLAN, can be extended if data available)
        $result['mealPlansRes'] = [MealPlansEnum::NO_MEAL_PLAN->value];

        return $result;
    }

    protected function getExpediaHotelData($property): array
    {
        /** @var MappingCacheService $mappingCacheService */
        $mappingCacheService = app(MappingCacheService::class);
        $hashMapperExpedia = $mappingCacheService->getMappingsExpediaHashMap();
        $reversedHashMap = array_flip($hashMapperExpedia);
        $expediaCode = $reversedHashMap[$property->code] ?? null;

        $result = [
            'expediaCode' => $expediaCode,
            'roomsData' => [],
            'roomsOccupancy' => [],
            'numRooms' => 0,
            'attributes' => [],
            'mealPlansRes' => [MealPlansEnum::NO_MEAL_PLAN->value],
            'ratingSupplier' => 0,
        ];

        if (! $expediaCode) {
            Notification::make()
                ->title('Expedia hotel not found in the mapper.')
                ->danger()
                ->send();

            return $result;
        }

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
            $result['ratingSupplier'] = Arr::get($expediaMainData, 'rating', 0);
        }

        if (! empty($expediaData)) {
            $result['roomsData'] = Arr::get($expediaData, 'rooms', []);
            $statistics = Arr::get($expediaData, 'statistics', []);
            $result['roomsOccupancy'] = Arr::get($expediaData, 'rooms_occupancy', []);

            $attributesP1 = Arr::get($expediaData, 'amenities', []);
            $attributesP2 = Arr::get(Arr::get($expediaData, 'attributes', []), 'general', []);
            $attributesP3 = Arr::get($expediaData, 'themes', []);
            $attributes = array_merge($attributesP1, $attributesP2, $attributesP3);
            $result['attributes'] = collect($attributes)
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

            $result['numRooms'] = Arr::get($statistics, '52.value', 0);
            $allInclusive = Arr::get($expediaData, 'all_inclusive', []);
            $mealPlans = MealPlansEnum::values();
            $mealPlansRes = array_filter($allInclusive, fn ($value) => in_array($value, $mealPlans));
            $mealPlansRes = array_values($mealPlansRes) ?: [MealPlansEnum::NO_MEAL_PLAN->value];
            if ($mealPlansRes[0] === true || $mealPlansRes[0] === 'true') {
                $mealPlansRes = [MealPlansEnum::ALL_INCLUSIVE->value];
            }
            $result['mealPlansRes'] = $mealPlansRes;
        } else {
            Notification::make()
                ->title('Rooms not found')
                ->danger()
                ->send();
        }

        return $result;
    }
}
