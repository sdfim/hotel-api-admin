<?php

namespace Modules\HotelContentRepository\Actions\HotelRoom;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use Modules\HotelContentRepository\Models\ProductConsortiaAmenity;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;
use Modules\HotelContentRepository\Models\ProductFeeTax;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class CloneHotelRoom
{
    /**
     * Deep clone a room with ALL child entities.
     * The clone is always created as "primary" (no CRM/merge relations are carried over).
     */
    public function execute(HotelRoom $source): HotelRoom
    {
        // Preload relations to avoid N+1 and to freeze a consistent snapshot.
        $source->load([
            'galleries:id',
            'attributes:id',
            'relatedRooms:id',
        ]);

        return DB::transaction(function () use ($source) {
            // 1) Base attributes of the room
            $data = Arr::only($source->toArray(), [
                'hotel_id', 'name', 'description', 'area', 'max_occupancy',
                'bed_groups', 'room_views',  'related_rooms', 'supplier_codes',
            ]);

            // Unique/foreign identifiers should not be reused on the clone.
            // If 'external_code' is NOT NULL in DB, prefer '' instead of null.
            $data['external_code'] = null;
            $data['supplier_codes'] = null;
            $data['name'] .= ' (Clone '.now()->format('Y-m-d H:i').')';

            $clone = HotelRoom::create($data);

            // 2) BelongsToMany relations (no extra pivot attributes at the moment).
            $clone->galleries()->sync($source->galleries->pluck('id')->all());
            $clone->attributes()->sync($source->attributes->pluck('id')->all());
            $clone->relatedRooms()->sync($source->relatedRooms->pluck('id')->all());

            return $clone;
        });
    }
}
