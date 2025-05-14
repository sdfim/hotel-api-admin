<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRoomMerge extends Model
{
    protected $table = 'pd_hotel_room_merges';

    protected $fillable = [
        'new_room_id',
        'parent_room_id',
        'child_room_id',
        'overwritten_fields',
    ];

    protected $casts = [
        'overwritten_fields' => 'array',
    ];

    public function parentRoom(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'parent_room_id');
    }

    public function childRoom(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'child_room_id');
    }

    public function newRoom(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'new_room_id');
    }

    public function childRoomCrm(): BelongsTo
    {
        return $this->belongsTo(RoomCrm::class, 'child_room_id', 'room_id');
    }
}
