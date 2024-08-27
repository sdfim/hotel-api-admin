<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class Mapping extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'mappings';

    protected $fillable = [
      'giata_id',
      'supplier',
      'supplier_id',
      'match_percentage'
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
}
