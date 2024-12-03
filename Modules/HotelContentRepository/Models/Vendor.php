<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\VendorFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Modules\Insurance\Models\InsurancePlan;

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

    public function insurances(): HasMany
    {
        return $this->hasMany(InsurancePlan::class, 'vendor_id');
    }
    public function contactInformation()
    {
        return $this->morphOne(ContactInformation::class, 'contactable');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($vendor) {
            $vendor->products()->delete();
            $vendor->insurances()->delete();
        });
    }
}
