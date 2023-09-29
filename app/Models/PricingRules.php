<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRules extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'property', 'destination', 'travel_date', 'datetime', 'days', 'nights', 'supplier_id', 'rate_code', 'room_type', 'total_guests', 'room_guests', 'number_rooms', 'meal_plan', 'rating'
    ];

    public function suppliers()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }
}
