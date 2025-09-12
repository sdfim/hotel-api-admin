<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IcePortalPropertyAsset extends Model
{
    use HasFactory;

    protected $connection;

    protected $primaryKey = 'listingID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'listingID',
        'type',
        'name',
        'supplierId',
        'supplierChainCode',
        'supplierMappedID',
        'createdOn',
        'propertyLastModified',
        'contentLastModified',
        'makeLiveDate',
        'makeLiveBy',
        'editDate',
        'editBy',
        'addressLine1',
        'city',
        'country',
        'postalCode',
        'latitude',
        'longitude',
        'listingClassName',
        'regionCode',
        'phone',
        'publicationStatus',
        'publishedDate',
        'roomTypes',
        'meetingRooms',
        'iceListingQuantityScore',
        'iceListingSizeScore',
        'iceListingCategoryScore',
        'iceListingRoomScore',
        'iceListingScore',
        'bookingListingScore',
        'listingURL',
        'bookingURL',
        'assets',
    ];

    protected $table = 'ice_portal_property_assets';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $casts = [
        'createdOn' => 'datetime',
        'propertyLastModified' => 'datetime',
        'contentLastModified' => 'datetime',
        'makeLiveDate' => 'datetime',
        'publishedDate' => 'datetime',
        'roomTypes' => 'json',
        'meetingRooms' => 'json',
        'assets' => 'json',
    ];

    public $labelFields = [
        'roomTypes' => 'Room Types',
        'meetingRooms' => 'Meeting Rooms',
        'assets' => 'Assets',
    ];

    public function mapperGiata(): HasOne
    {
        return $this->hasOne(Mapping::class, 'supplier_id', 'listingID')->icePortal();
    }

    public function getHasRoomTypesAttribute(): bool
    {
        return ! empty($this->roomTypes);
    }

    public function mapperHbsiGiata(): HasMany
    {
        return $this->hasMany(Mapping::class, 'supplier_id', 'listingID')->icePortal();
    }
}
