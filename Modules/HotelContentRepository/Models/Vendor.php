<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\VendorFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class Vendor extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return VendorFactory::new();
    }

    protected $table = 'pd_vendors';

    protected $fillable = [
        'name',
        'verified',
        'address',
        'lat',
        'lng',
        'website',
    ];

    protected $casts = [
        'address' => 'array',
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }
}
