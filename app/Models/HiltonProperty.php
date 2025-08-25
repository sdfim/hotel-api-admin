<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HiltonProperty extends Model
{
    use HasFactory;

    protected $table = 'hilton_properties';

    protected $fillable = [
        'prop_code',
        'name',
        'facility_chain_name',
        'city',
        'country_code',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'phone_number',
        'email',
        'website',
        'star_rating',
        'market_tier',
        'year_built',
        'opening_date',
        'time_zone',
        'checkin_time',
        'checkout_time',
        'allow_adults_only',
        'props',
        'policy',
        'taxes',
        'meeting_rooms',
        'safety_and_security',
        'location_details',
        'area_locators',
        'nearby_corporations',
        'nearby_points',
        'guest_room_descriptions',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'year_built' => 'integer',
        'opening_date' => 'date',
        'checkin_time' => 'string',
        'checkout_time' => 'string',
        'allow_adults_only' => 'boolean',
        'props' => 'array',
        'policy' => 'array',
        'taxes' => 'array',
        'meeting_rooms' => 'array',
        'safety_and_security' => 'array',
        'location_details' => 'array',
        'area_locators' => 'array',
        'nearby_corporations' => 'array',
        'nearby_points' => 'array',
        'guest_room_descriptions' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    public static function getShortListFields(): array
    {
        return ['prop_code', 'name', 'city', 'country_code', 'address', 'postal_code', 'latitude',
            'longitude', 'phone_number', 'email', 'website', 'star_rating', 'checkin_time', 'checkout_time',
            'allow_adults_only', 'props', 'taxes', 'location_details', 'guest_room_descriptions'];
    }

    public static function getFullListFields(): array
    {
        return ['prop_code', 'name', 'facility_chain_name', 'city', 'country_code', 'address', 'postal_code',
            'latitude', 'longitude', 'phone_number', 'email', 'website', 'star_rating', 'market_tier',
            'year_built', 'opening_date', 'time_zone', 'checkin_time', 'checkout_time', 'allow_adults_only',
            'props', 'policy', 'taxes', 'meeting_rooms', 'safety_and_security', 'location_details',
            'area_locators', 'nearby_corporations', 'nearby_points', 'guest_room_descriptions',
        ];
    }

    public function getHasPropsAttribute(): bool
    {
        return ! empty($this->props);
    }

    public function mapperHiltonGiata(): HasMany
    {
        return $this->hasMany(Mapping::class, 'supplier_id', 'prop_code')->hilton();
    }
}
