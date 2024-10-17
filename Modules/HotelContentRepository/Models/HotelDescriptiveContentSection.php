<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelDescriptiveContentSectionFactory;

class HotelDescriptiveContentSection extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelDescriptiveContentSectionFactory::new();
    }

    protected $table = 'pd_hotel_descriptive_content_sections';

    protected $fillable = [
        'hotel_id',
        'section_name',
        'start_date',
        'end_date',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function content()
    {
        return $this->hasMany(HotelDescriptiveContent::class, 'content_sections_id');
    }
}
