<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTraderContentRatePlanPush extends Model
{
    use HasFactory;

    protected $table = 'hotel_trader_content_rate_plans';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $fillable = [
        'hotel_code',
        'code',
        'name',
        'currency', // JSON
        'short_description',
        'detail_description',
        'cancellation_policy_code',
        'mealplan', // JSON
        'is_tax_inclusive',
        'is_refundable',
        'rateplan_type', // JSON
        'is_promo',
        'destination_exclusive', // JSON
        'destination_restriction', // JSON nullable
        'seasonal_policies', // JSON
    ];

    protected $casts = [
        'currency' => 'array',
        'mealplan' => 'array',
        'rateplan_type' => 'array',
        'destination_exclusive' => 'array',
        'destination_restriction' => 'array',
        'seasonal_policies' => 'array',
        'is_tax_inclusive' => 'boolean',
        'is_refundable' => 'boolean',
        'is_promo' => 'boolean',
    ];
}
