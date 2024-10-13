<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelDescriptiveContentFactory;

class HotelDescriptiveContent extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelDescriptiveContentFactory::new();
    }

    protected $table = 'pd_hotel_descriptive_content';

    protected $fillable = [
        'hotel_id',
        'section_name',
        'meta_description',
        'property_description',
        'cancellation_policy',
        'pet_policy',
        'terms_conditions',
        'fees_paid_at_hotel',
        'staff_contact_info',
        'validity_start',
        'validity_end',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
