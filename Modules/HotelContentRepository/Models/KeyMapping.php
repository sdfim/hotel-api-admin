<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\KeyMappingFactory;

class KeyMapping extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return KeyMappingFactory::new();
    }

    protected $table = 'pd_key_mapping';

    protected $fillable = [
        'hotel_id',
        'key_id',
        'key_name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
