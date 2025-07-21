<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\ImageSectionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ImageSection extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ImageSectionFactory::new();
    }

    protected $table = 'pd_image_sections';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
