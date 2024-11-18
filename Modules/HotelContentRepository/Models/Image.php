<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\ImageFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class Image extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ImageFactory::new();
    }

    protected $table = 'pd_images';

    protected $fillable = [
        'image_url',
        'tag',
        'weight',
        'section_id',
        ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function section()
    {
        return $this->belongsTo(ImageSection::class, 'section_id');
    }

    public function galleries()
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_gallery_images', 'image_id', 'gallery_id');
    }
}
