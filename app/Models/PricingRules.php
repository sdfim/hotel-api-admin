<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRules extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'property', 'destination', 'travel_date', 'datetime', 'days', 'nights', 'supplier_id', 'rate_code', 'room_type', 'total_guests', 'room_guests', 'number_rooms', 'meal_plan', 'rating',
        'manipulate_data_id' ,  'manipulate_item_id' ,  'manipulate_type_id' 
    ];

    public function suppliers()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    public function manipulateData()
    {
        return $this->belongsTo(ManipulateData::class, 'manipulate_data_id');
    }

    public function manipulateType()
    {
        return $this->belongsTo(ManipulateType::class, 'manipulate_type_id');
    }

    public function manipulateItem()
    {
        return $this->belongsTo(ManipulateItem::class, 'manipulate_item_id');
    }
}
