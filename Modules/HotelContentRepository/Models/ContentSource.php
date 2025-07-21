<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Models\Factories\ContentSourceFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ContentSource extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ContentSourceFactory::new();
    }

    protected $table = 'pd_content_sources';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function getContentSources()
    {
        return static::where('name', '!=', ContentSourceEnum::HBSI->value);
    }
}
