<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HotelTraderProperty extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_properties';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'propertyId',
        'propertyName',
        'address1',
        'address2',
        'city',
        'state',
        'countryCode',
        'zipCode',
        'starRating',
        'guestRating',
        'latitude',
        'longitude',
        'phone1',
        'longDescription',
        'rooms',
    ];

    protected $casts = [
        'rooms' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public static function getShortListFields(): array
    {
        return [
            'propertyId',
            'propertyName',
            'address1',
            'city',
            'state',
            'countryCode',
            'zipCode',
            'starRating',
            'guestRating',
            'latitude',
            'longitude',
        ];
    }

    public static function getFullListFields(): array
    {
        return [
            'propertyId',
            'propertyName',
            'address1',
            'address2',
            'city',
            'state',
            'countryCode',
            'zipCode',
            'starRating',
            'guestRating',
            'latitude',
            'longitude',
            'phone1',
            'longDescription',
            'rooms',
        ];
    }

    public function getHasRoomTypesAttribute(): bool
    {
        return ! empty($this->rooms);
    }

    public function mapperHbsiGiata(): HasMany
    {
        return $this->hasMany(Mapping::class, 'supplier_id', 'propertyId')->hotelTrader();
    }
}
