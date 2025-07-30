<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTraderContentTax extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_content_taxes';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'hotel_code',
        'code',
        'name',
        'percent_or_flat',
        'charge_frequency',
        'charge_basis',
        'value',
        'tax_type',
        'applies_to_children',
        'pay_at_property',
    ];

    protected $casts = [
        'applies_to_children' => 'boolean',
        'pay_at_property' => 'boolean',
        'value' => 'float',
    ];
}

