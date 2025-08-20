<?php

namespace App\Jobs\AddHotel;

use App\Models\Configurations\ConfigAttribute;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\HotelContentRepository\Models\Hotel;

class ProcessHotelRoomsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Hotel $hotel, public array $dataSupplier, public int $giataId, public User $recipient, public array $dataForm) {}

    public function handle()
    {
        Notification::make()
            ->title('Started writing rooms data to the database...')
            ->info()
            ->broadcast($this->recipient);

        $aiSupplierCodes = $this->fetchAiSupplierCodes();
        $this->processRooms($aiSupplierCodes);

        Notification::make()
            ->title('Rooms data written successfully.')
            ->info()
            ->broadcast($this->recipient);
    }

    protected function fetchAiSupplierCodes(): array
    {
        // Retrieve merged data from cache
        $cacheKey = 'supplier_merge_data'.($this->giataId ? "_{$this->giataId}" : '');
        $mergedDataArray = Cache::get($cacheKey) ?? [];

        logger()->info('LoggerFlowHotel _ ProcessHotelRoomsJob 1 ', ['mergedDataArray' => $mergedDataArray, 'dataForm' => $this->dataForm]);

        $aiSupplierCodes = [];
        foreach ($mergedDataArray as $mergedRoom) {
            $supplierCodes = [];
            $externalCode = '';
            foreach ($mergedRoom['listings_to_merge'] as $listing) {
                $supplierCodes[] = [
                    'code' => $listing['code'],
                    'name' => $listing['name'],
                    'supplier' => $listing['supplier'],
                ];
                if ($listing['supplier'] === $this->dataForm['main_supplier']) {
                    $externalCode = $listing['code'];
                }
            }
            $aiSupplierCodes[$externalCode]['supplier_codes'] = json_encode($supplierCodes, JSON_UNESCAPED_UNICODE);
            $aiSupplierCodes[$externalCode]['external_code'] = 'external_'.$externalCode;
        }

        logger()->info('LoggerFlowHotel _ ProcessHotelRoomsJob 2 ', ['aiSupplierCodes' => $aiSupplierCodes]);

        return $aiSupplierCodes;
    }

    protected function processRooms(array $aiSupplierCodes): void
    {
        foreach ($this->dataSupplier['roomsData'] as $room) {
            $roomId = Arr::get($room, 'id', 0);
            $description = Arr::get($room, 'descriptions.overview');
            $descriptionAfterLayout = preg_replace('/^<p>.*?<\/p>\s*<p>.*?<\/p>\s*/', '', $description);
            $maxRoomOccupancy = Arr::get($this->dataSupplier['roomsOccupancy'], $roomId.'.occupancy.max_allowed.total', 0);

            $roomSupplierCodes = Arr::get($room, 'supplier_codes') ?? json_encode([['code' => Arr::get($room, 'id'), 'supplier' => $room['supplier']]]);
            $roomSupplierCodes = ! empty($aiSupplierCodes) && isset($aiSupplierCodes[$roomId])
                ? $aiSupplierCodes[$roomId]['supplier_codes']
                : $roomSupplierCodes;

            $hotelRoom = $this->hotel->rooms()->updateOrCreate(
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
                $attribute = ConfigAttribute::firstOrCreate([
                    'name' => $amenityName,
                    'default_value' => $amenityName.' room',
                ]);
                $attributeIds[] = $attribute->id;
            }
            $hotelRoom->attributes()->sync($attributeIds);
        }
    }
}
