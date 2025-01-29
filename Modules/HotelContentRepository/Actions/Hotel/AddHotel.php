<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use App\Models\Configurations\ConfigAttribute;
use App\Models\Property;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\API\Services\MappingCacheService;
use Modules\Enums\ContentSourceEnum;
use Modules\Enums\MealPlansEnum;
use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Events\Hotel\HotelAdded;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;

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
        $numRooms = 0;
        $mealPlansRes = [MealPlansEnum::NO_MEAL_PLAN->value];
        if ($expediaCode) {
            $rooms = DB::connection('mysql_cache')
                ->table('expedia_content_slave')
                ->select('rooms', 'statistics', 'all_inclusive')
                ->where('expedia_property_id', $expediaCode)
                ->get();
            if ($rooms->isEmpty()) {
                Notification::make()
                    ->title('Rooms not found')
                    ->danger()
                    ->send();
            } else {
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
        }

        return DB::transaction(function () use ($property, $vendorId, $source_id, $roomsData, $numRooms, $mealPlansRes) {
            $hotel = Hotel::create([
                'giata_code' => $property->code,
                'star_rating' => max($property->rating ?? 1, 1),
                'sale_type' => 'Direct Connection',
                'num_rooms' => $numRooms,
                'hotel_board_basis' => $mealPlansRes,
                'room_images_source_id' => $source_id,
                'address' => [
                    'line_1' => $property->mapper_address ?? '',
                    'city' => $property->city ?? '',
                    'country_code' => $property->address->CountryName ?? '',
                    'state_province_name' => $property->address->AddressLine ?? '',
                ],
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

            if (! empty($roomsData)) {
                foreach ($roomsData as $room) {
                    $description = Arr::get($room, 'descriptions.overview');
                    $descriptionAfterLayout = preg_replace('/^<p>.*?<\/p>\s*<p>.*?<\/p>\s*/', '', $description);
                    $hotelRoom = $hotel->rooms()->create([
                        'name' => Arr::get($room, 'name'),
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

                        if ($k > 10) {
                            break;
                        }
                    }
                    // Attach the attribute IDs to the room
                    $hotelRoom->attributes()->sync($attributeIds);
                }
            }

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
}
