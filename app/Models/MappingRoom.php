<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class MappingRoom extends Model
{
    use HasFactory;

    protected $connection;

    protected $table = 'mapping_rooms';

    protected $fillable = [
        'giata_id',
        'unified_room_code',
        'supplier',
        'supplier_room_code',
        'supplier_room_name',
        'match_percentage',
    ];

    public function scopeExpedia($query)
    {
        return $query->where('supplier', MappingSuppliersEnum::Expedia->value);
    }

    public function scopeHBSI($query)
    {
        return $query->where('supplier', MappingSuppliersEnum::HBSI->value);
    }

    public function scopeIcePortal($query)
    {
        return $query->where('supplier', MappingSuppliersEnum::IcePortal->value);
    }

    public function scopeHilton($query)
    {
        return $query->where('supplier', MappingSuppliersEnum::HILTON->value);
    }
}
