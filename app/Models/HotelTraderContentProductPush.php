<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTraderContentProductPush extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_content_products';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'hotel_code',
        'rateplan_code',
        'roomtype_code',
        'taxes', // JSON array of tax codes
    ];

    protected $casts = [
        'taxes' => 'array',
    ];
}
