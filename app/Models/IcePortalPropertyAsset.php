<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IcePortalPropertyAsset extends Model
{
    use HasFactory;

    protected $connection;

    public $incrementing = false;

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

    public function mapperGiata(): HasOne
    {
        return $this->hasOne(Mapping::class, 'supplier_id', 'listingID')->icePortal();
    }
}
