<?php

namespace Modules\HotelContentRepository\Models;

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
        'direct_connection',
        'manual_contract',
        'commission_tracking',
        'address',
        'star_rating',
        'website',
        'num_rooms',
        'featured',
        'location',
        'content_source_id',
        'room_images_source_id',
        'property_images_source_id',
        'channel_management',
        'hotel_board_basis',
        'default_currency',
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

    public function descriptiveContents()
    {
        return $this->hasMany(HotelDescriptiveContent::class);
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

    public function travelAgencyCommissions()
    {
        return $this->hasMany(TravelAgencyCommission::class);
    }

    public function galleries()
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_hotel_gallery', 'hotel_id', 'gallery_id');
    }
}
