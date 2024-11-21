<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\HotelContentRepository\Models\Factories\ProductFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class Product extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    protected $table = 'pd_products';

    protected $fillable = [
        'vendor_id',
        'product_type',
        'name',
        'verified',
        'content_source_id',
        'property_images_source_id',
        'default_currency',
        'website',
        'location',
        'lat',
        'lng',
        'related_id',
        'related_type'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'location'
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function contentSource()
    {
        return $this->belongsTo(ContentSource::class, 'content_source_id');
    }

    public function propertyImagesSource()
    {
        return $this->belongsTo(ContentSource::class, 'property_images_source_id');
    }

    public function affiliations()
    {
        return $this->hasMany(ProductAffiliation::class);
    }

    public function ageRestrictions()
    {
        return $this->hasMany(ProductAgeRestriction::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function descriptiveContentsSection()
    {
        return $this->hasMany(ProductDescriptiveContentSection::class);
    }

    public function feeTaxes()
    {
        return $this->hasMany(ProductFeeTax::class);
    }

    public function informativeServices()
    {
        return $this->hasMany(ProductInformativeService::class);
    }

    public function promotions()
    {
        return $this->hasMany(ProductPromotion::class);
    }

    public function keyMappings()
    {
        return $this->hasMany(KeyMapping::class);
    }

    public function galleries()
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_product_gallery', 'product_id', 'gallery_id');
    }

    public function contactInformation()
    {
        return $this->hasMany(ProductContactInformation::class);
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
     *
     * @return array
     */

    public function getLocationAttribute(): array
    {
        return [
            'lat' => (float)$this->lat,
            'lng' => (float)$this->lng,
        ];
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'lat' and 'lng' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location' attribute be included in this model's $fillable array.
     *
     * @param ?array $location
     * @return void
     */
    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
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
     *
     * @return string
     */
    public static function getComputedLocation(): string
    {
        return 'location';
    }
}
