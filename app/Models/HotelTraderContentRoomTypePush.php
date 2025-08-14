<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTraderContentRoomTypePush extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_content_room_types';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'hotel_code',
        'code',
        'name',
        'long_description',
        'short_description',
        'max_adult_occupancy',
        'min_adult_occupancy',
        'max_child_occupancy',
        'min_child_occupancy',
        'total_max_occupancy',
        'max_occupancy_for_default_price',
        'bedtypes', // JSON
        'amenities', // JSON
        'images', // JSON
    ];

    protected $casts = [
        'bedtypes' => 'array',
        'amenities' => 'array',
        'images' => 'array',
    ];
}
