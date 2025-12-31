<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class Mapping extends Model
{
    use HasFactory;

    protected $connection;

    protected $table = 'mappings';

    protected $fillable = [
        'giata_id',
        'supplier',
        'supplier_id',
        'match_percentage',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $mainDB = config('database.connections.mysql.database');
        $this->table = "$mainDB.mappings";
        $this->connection = config('database.active_connections.mysql');
    }

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

    public function scopeHotelTrader($query)
    {
        return $query->where('supplier', MappingSuppliersEnum::HOTEL_TRADER->value);
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'giata_id', 'code');
    }
}
