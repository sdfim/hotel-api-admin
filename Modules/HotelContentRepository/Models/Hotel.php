<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelFactory;

class Hotel extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelFactory::new();
    }

    protected $table = 'pd_hotels';

    protected $fillable = [
        'name',
        'type',
        'verified',
        'address',
        'star_rating',
        'website',
        'num_rooms',
        'verified',
        'location',
        'content_source_id',
        'room_images_source_id',
        'property_images_source_id',
        'channel_management',
        'hotel_board_basis',
        'default_currency',
        'location_gm',
        'lat',
        'lng',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'address' => 'array',
        'location' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function contentSource()
    {
        return $this->belongsTo(ContentSource::class, 'content_source_id');
    }

    public function roomImagesSource()
    {
        return $this->belongsTo(ContentSource::class, 'room_images_source_id');
    }

    public function propertyImagesSource()
    {
        return $this->belongsTo(ContentSource::class, 'property_images_source_id');
    }

    public function affiliations()
    {
        return $this->hasMany(HotelAffiliation::class);
    }

    public function attributes()
    {
        return $this->hasMany(HotelAttribute::class);
    }

    public function descriptiveContentsSection()
    {
        return $this->hasMany(HotelDescriptiveContentSection::class);
    }

    public function feeTaxes()
    {
        return $this->hasMany(HotelFeeTax::class);
    }

    public function informativeServices()
    {
        return $this->hasMany(HotelInformativeService::class);
    }

    public function promotions()
    {
        return $this->hasMany(HotelPromotion::class);
    }

    public function rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }

    public function keyMappings()
    {
        return $this->hasMany(KeyMapping::class);
    }

    public function galleries()
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_hotel_gallery', 'hotel_id', 'gallery_id');
    }

    public function contactInformation()
    {
        return $this->hasMany(HotelContactInformation::class);
    }

    /**
     * ADD THE FOLLOWING METHODS TO YOUR Modules\HotelContentRepository\Models\Hotel MODEL
     *
     * The 'lat' and 'lng' attributes should exist as fields in your table schema,
     * holding standard decimal latitude and longitude coordinates.
     *
     * The 'location_gm' attribute should NOT exist in your table schema, rather it is a computed attribute,
     * which you will use as the field name for your Filament Google Maps form fields and table columns.
     *
     * You may of course strip all comments, if you don't feel verbose.
     */

    /**
     * Returns the 'lat' and 'lng' attributes as the computed 'location_gm' attribute,
     * as a standard Google Maps style Point array with 'lat' and 'lng' attributes.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location_gm' attribute be included in this model's $fillable array.
     *
     * @return array
     */

    public function getLocationGmAttribute(): array
    {
        return [
            "lat" => (float)$this->lat,
            "lng" => (float)$this->lng,
        ];
    }

    /**
     * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
     * 'lat' and 'lng' attributes on this model.
     *
     * Used by the Filament Google Maps package.
     *
     * Requires the 'location_gm' attribute be included in this model's $fillable array.
     *
     * @param ?array $location
     * @return void
     */
    public function setLocationGmAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['lat'] = $location['lat'];
            $this->attributes['lng'] = $location['lng'];
            unset($this->attributes['location_gm']);
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
        return 'location_gm';
    }
}
