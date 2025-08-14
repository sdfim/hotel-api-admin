<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTraderContentHotelPush extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_content_hotels';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'code',
        'mapping_providers',
        'name',
        'star_rating',
        'default_currency_code',
        'max_rooms_bookable',
        'number_of_rooms',
        'number_of_floors',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'state_code',
        'country',
        'country_code',
        'zip',
        'phone_1',
        'phone_2',
        'fax_1',
        'fax_2',
        'website_url',
        'longitude',
        'latitude',
        'long_description',
        'short_description',
        'check_in_time',
        'check_out_time',
        'time_zone',
        'adult_age',
        'default_language',
        'adult_only',
        'currencies',
        'languages',
        'credit_card_types',
        'bed_types',
        'amenities',
        'age_categories',
        'check_in_policy',
        'images',
    ];

    protected $casts = [
        'mapping_providers' => 'array',
        'star_rating' => 'float',
        'max_rooms_bookable' => 'integer',
        'number_of_rooms' => 'integer',
        'number_of_floors' => 'integer',
        'adult_age' => 'integer',
        'adult_only' => 'boolean',
        'currencies' => 'array',
        'languages' => 'array',
        'credit_card_types' => 'array',
        'bed_types' => 'array',
        'amenities' => 'array',
        'age_categories' => 'array',
        'images' => 'array',
    ];

    public function rooms()
    {
        return $this->hasMany(HotelTraderContentRoomType::class, 'hotel_code', 'code');
    }

    public function rates()
    {
        return $this->hasMany(HotelTraderContentRatePlan::class, 'hotel_code', 'code');
    }

    public function cellationPolicies()
    {
        return $this->hasMany(HotelTraderContentCancellationPolicyPush::class, 'hotel_code', 'code');
    }

    public function taxes()
    {
        return $this->hasMany(HotelTraderContentTax::class, 'hotel_code', 'code');
    }

    public static function getShortListFields(): array
    {
        return ['code', 'name', 'city', 'country_code', 'address_line_1', 'address_line_2', 'zip', 'latitude',
            'longitude', 'phone_1', 'phone_2', 'fax_1', 'fax_2', 'website_url', 'star_rating',
            'check_in_time', 'check_out_time', 'adult_only', 'currencies', 'languages',
            'credit_card_types', 'bed_types', 'amenities', 'age_categories'];
    }

    public static function getFullListFields(): array
    {
        return ['code', 'name', 'star_rating', 'default_currency_code', 'max_rooms_bookable', 'number_of_rooms',
            'number_of_floors', 'address_line_1', 'address_line_2', 'city', 'state', 'state_code',
            'country', 'country_code', 'zip', 'phone_1', 'phone_2', 'fax_1', 'fax_2',
            'website_url', 'longitude', 'latitude', 'long_description', 'short_description',
            'check_in_time', 'check_out_time', 'time_zone', 'adult_age', 'default_language',
            'adult_only', 'currencies', 'languages', 'credit_card_types', 'bed_types',
            'amenities', 'age_categories', 'check_in_policy', 'images',
            'rooms', 'rates', 'cellation_policies', 'taxes',
        ];
    }
}
