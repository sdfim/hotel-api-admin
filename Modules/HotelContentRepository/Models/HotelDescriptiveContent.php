<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigDescriptiveType;
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
        'content_sections_id',
        'descriptive_type_id',
        'value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function contentSection()
    {
        return $this->belongsTo(HotelDescriptiveContentSection::class, 'content_sections_id');
    }

    public function descriptiveType()
    {
        return $this->belongsTo(ConfigDescriptiveType::class, 'descriptive_type_id');
    }
}
