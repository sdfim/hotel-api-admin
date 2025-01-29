<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\HotelContentRepository\Models\Factories\VendorFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Modules\Insurance\Models\InsurancePlan;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vendor extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

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
        'location',
        'independent_flag',
        'type',
    ];

    protected $casts = [
        'address' => 'array',
        'type' => 'array',
        'lat' => 'float',
        'lng' => 'float',
        'verified' => 'boolean',
        'independent_flag' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'location',
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

    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_vendor_gallery', 'vendor_id', 'gallery_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($vendor) {
            $vendor->products()->delete();
            $vendor->insurances()->delete();
        });
    }

    public function team(): HasOne
    {
        return $this->hasOne(Team::class);
    }

    protected $appends = [
        'location',
    ];

    /**
     * ADD THE FOLLOWING METHODS TO YOUR Modules\HotelContentRepository\Models\Product MODEL
     *
     * The 'lat' and 'lng' attributes should exist as fields in your table schema,
     * holding standard decimal latitude and longitude coordinates.
     *
     * The 'location' attribute should NOT exist in your table schema, rather it is a computed attribute,
     * which you will use as the field name for your Filament Google Maps form fields and table columns.
     *
     * You may of course strip all comments, if you don't feel verbose.
     */

    /**
     * Returns the 'lat' and 'lng' attributes as the computed 'location' attribute,
     * as a standard Google Maps style Point array with 'lat' and 'lng' attributes.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     */
    public function getLocationAttribute(): array
    {
        return [
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
        ];
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'lat' and 'lng' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location)) {
            $this->attributes['lat'] = $location['lat'];
            $this->attributes['lng'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'lat',
            'lng' => 'lng',
        ];
    }

    /**
     * Get the name of the computed location attribute
     *
     * Used by the Filament Google Maps package.
     */
    public static function getComputedLocation(): string
    {
        return 'location';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'verified', 'address', 'lat', 'lng', 'website'])
            ->logOnlyDirty()
            ->useLogName('vendor');
    }
}
