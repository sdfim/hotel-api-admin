<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'alt',
        'source',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ImageSection::class, 'section_id');
    }

    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_gallery_images', 'image_id', 'gallery_id');
    }

    // In your Image model
    public function scopeOrderByWeightAsNumber($query, $direction = 'asc')
    {
        return $query->orderByRaw("CAST(weight AS UNSIGNED) {$direction}");
    }

    public function getFullUrlAttribute()
    {
        return match ($this->source) {
            'crm' => config('image_sources.sources.crm').$this->image_url,
            'own' => config('filesystems.default') === 's3'
                ? config('image_sources.sources.s3').$this->image_url
                : config('image_sources.sources.local').'/storage/'.$this->image_url,
            default => $this->image_url,
        };
    }
}
